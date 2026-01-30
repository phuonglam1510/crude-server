<?php


namespace App\Services\House;

use App\Helpers\ImageHelper;
use App\Helpers\S3Helper;
use App\Jobs\RemoveImage;
use App\Repositories\House\HouseRepository;
use App\Repositories\House\ImageRepository;
use App\Http\Traits\CustomResponse;
use Chumper\Zipper\Facades\Zipper;

class ImageService
{
    use CustomResponse;
    public $imageRepo;
    public $imageHelper;
    public $S3Helper;
    public $houseRepo;
    protected $houseZip = '/zip/houses/';

    public function __construct(
        ImageRepository $imageRepository,
        ImageHelper $imageHelper,
        S3Helper $s3Helper,
        HouseRepository $houseRepository
    ) {
        $this->imageRepo = $imageRepository;
        $this->imageHelper = $imageHelper;
        $this->S3Helper = $s3Helper;
        $this->houseRepo = $houseRepository;
    }

    /**
     * Remove image from S3
     * @param $imageId
     * @param $file
     * @return bool|null
     */
    public function updateImage($imageId, $file)
    {
        $imageData = $this->imageRepo->getModelById($imageId);

        if ($imageData) {
            $mainPath = $imageData->main;
            $thumbPath = $imageData->thumbnail;

            if ($this->S3Helper->checkS3Path($mainPath)) {
                $fileName = last(explode('/', $mainPath));

                $mainPath = explode('uploads', $mainPath);
                $mainPath = last($mainPath);
                $mainPath = "uploads{$mainPath}";

                $thumbPath = explode('uploads', $thumbPath);
                $thumbPath = last($thumbPath);
                $thumbPath = "uploads{$thumbPath}";

                $imageData = $this->imageHelper->getImageWithoutMasking($file, $fileName);
                $mime = $imageData['mime'];

                $mainUrl = $this->S3Helper->uploadImage(public_path($imageData['main']), $mainPath, $mime);
                $thumbnailUrl = $this->S3Helper->uploadImage(public_path($imageData['thumbnail']), $thumbPath, $mime);

                if ($mainUrl) {
                    $this->imageRepo->update($imageId, ['main' => $mainUrl, 'thumbnail' => $mainUrl]);

                    // Remove local files 
                    RemoveImage::dispatch([
                        'main' => public_path($imageData['main']),
                        'thumbnail' => public_path($imageData['thumbnail'])
                    ])->delay(now()->addSecond(10));

                    return [
                        'main' => $mainUrl,
                        'thumbnail' => $thumbnailUrl
                    ];
                }
                return null;
            }
            return null;
        }

        return null;
    }

    /**
     * Upload image with mime
     * @param $file
     * @param $rotate
     * @return mixed|null
     */
    public function addImage($file, $rotate, $newName)
    {
        $imageData = $this->imageHelper->getImage($file, $rotate, $newName);
        //dd($imageData);
        $main = $imageData['main'];
        $thumbnail = $imageData['thumbnail'];

        if ($imageData) {
            $data['main'] = $this->S3Helper->uploadImage(public_path($imageData['main']), $imageData['main'], $imageData['mime']);
            $data['thumbnail'] = $this->S3Helper->uploadImage(
                public_path($imageData['thumbnail']),
                $imageData['thumbnail'],
                $imageData['mime']
            );

            if (!$data['main'] && !$data['thumbnail']) {
                return null;
            }

            $image = $this->imageRepo->create($data);
            // Remove main
            RemoveImage::dispatch([
                'main' => public_path($main),
                'thumbnail' => public_path($thumbnail)
            ])->delay(now()->addSecond(10));

            return [
                'image' => $image,
                'main' => $main,
                'thumbnail' => $thumbnail
            ];
        }

        return null;
    }

    public function rotateImage($id, $rotate)
    {
        $imageData = $this->imageRepo->getModelById($id);

        if ($imageData) {
            $mainPath = $imageData->main;
            $thumbPath = $imageData->thumbnail;

            if ($this->S3Helper->checkS3Path($mainPath)) {
                $mainData = $this->S3Helper->storeS3File($mainPath);
                $thumbData = $this->S3Helper->storeS3File($thumbPath);

                if ($mainData && $thumbData) {
                    $extension = $mainData['extension'];
                    $mime = "image/" . $extension;
                    $mainPath = $mainData['target'];
                    $thumbPath = $thumbData['target'];

                    if (file_exists($mainPath) && file_exists($thumbPath)) {
                        try {
                            // Rotate local
                            $this->imageHelper->rotateImage($mainPath, $rotate, $extension);
                            $this->imageHelper->rotateImage($thumbPath, $rotate, $extension);

                            // ReUpload to S3
                            $this->S3Helper->uploadImage(public_path($mainPath), $mainPath, $mime);
                            $this->S3Helper->uploadImage(public_path($thumbPath), $thumbPath, $mime);

                            // Remove main
                            RemoveImage::dispatch([
                                'main' => public_path($mainPath),
                                'thumbnail' => public_path($thumbPath)
                            ])->delay(now()->addSecond(10));

                            return $imageData;
                        } catch (\Exception $exception) {
                            $this->error($exception->getMessage());
                        }
                    } else {
                        $this->error(config('API.Message.ImageNotExisted'));
                    }
                } else {
                    $this->error(config('API.Message.ImageNotExisted'));
                }
            } else {
                $localPath = public_path() . '/' . $imageData->main;
                if (file_exists($localPath)) {
                    $extension = pathinfo($localPath, PATHINFO_EXTENSION);

                    $this->imageHelper->rotateImage($imageData->main, $rotate, $extension);

                    if (!empty($imageData->thumbnail)) {
                        $this->imageHelper->rotateImage($imageData->thumbnail, $rotate, $extension);
                    }

                    return $imageData;
                } else {
                    $this->error(config('API.Message.ImageNotExisted'));
                }
            }
        } else {
            $this->error(config('API.Message.ImageNotExisted'));
        }

        return null;
    }

    public function zipImageFolders($houseIds)
    {
        $groupFolderPath = implode("_", $houseIds);
        $groupPublicFolderPath = public_path($this->houseZip . $groupFolderPath);

        if (!is_dir($groupPublicFolderPath)) {
            mkdir($groupPublicFolderPath, 0777, true);
        }

        foreach ($houseIds as $houseId) {
            $zipFolderPath = public_path($this->zipImageFolder($houseId));

            $house = $this->houseRepo->getModelById($houseId);
            $fileName =  str_replace("/", "_", $house->house_number) . '_' .  str_replace(" ", "_", $house->house_address) . '.zip';

            // Create a zip file contains both public and internal zip files
            $files = glob($zipFolderPath . '/*');
            $houseZipFilePath = $groupPublicFolderPath . '/' . $fileName;
            Zipper::make($houseZipFilePath)->add($files)->close();

            // Clean public and internal zip files
            $this->removeImages($zipFolderPath);
        }

        // Create final zip file contains all house zip files in path /public/zip/houses/[name].zip
        $finalFiles = glob($groupPublicFolderPath . '/*');
        $finalZipFilePath = public_path($this->houseZip) . $groupFolderPath . '.zip';
        Zipper::make($finalZipFilePath)->add($finalFiles)->close();

        // Clean zip file for each house
        $this->removeImages($groupPublicFolderPath);

        return $this->houseZip . $groupFolderPath . '.zip';
    }

    public function zipImageFolder($houseId)
    {
        $house = $this->houseRepo->getModelById($houseId);
        $publicImage = $house->public_image;
        $internalImage = $house->internal_image;

        try {
            $publicImage = $this->imageRepo->getModelByFields([
                ['id', 'IN', $publicImage]
            ]);

            $internalImage = $this->imageRepo->getModelByFields([
                ['id', 'IN', $internalImage]
            ]);

            // Prepare empty folder for public and private folder with format /public/[houseId]/(internal|public)
            $this->createZipFolder($houseId, $housePath, $publicPath, $internalPath);

            $listPublic = array_column($publicImage->toArray(), 'main');
            $listInternal = array_column($internalImage->toArray(), 'main');

            // For S3 storage files, download them into public folder. For local files, copy them into public folder.
            foreach ($listPublic as $path) {
                if ($this->S3Helper->checkS3Path($path)) {
                    $this->S3Helper->storeS3File($path, $publicPath);
                } else {
                    copy($path, $publicPath . '/' . basename($path));
                }
            }

            $files = glob($publicPath . '/*');
            $publicZipPath = $housePath . '/public.zip';
            Zipper::make($publicZipPath)->add($files)->close();

            foreach ($listInternal as $path) {
                if ($this->S3Helper->checkS3Path($path)) {
                    $this->S3Helper->storeS3File($path, $internalPath);
                } else {
                    copy($path, $internalPath . '/' . basename($path));
                }
            }

            $files = glob($internalPath . '/*');
            $internalZipPath = $housePath . '/internal.zip';
            Zipper::make($internalZipPath)->add($files)->close();

            // Remove public & internal folder
            $this->removeImages($publicPath);
            $this->removeImages($internalPath);

            return $this->houseZip . $houseId;
        } catch (\Exception $exception) {
            return false;
        }
    }

    public function createZipFolder($houseId, &$housePath, &$publicPath, &$internalPath)
    {
        $housePath = public_path($this->houseZip . $houseId);

        if (!is_dir($housePath)) {
            mkdir($housePath, 0777, true);
        }

        $publicPath = $housePath . '/public';
        $internalPath = $housePath . '/internal';

        if (!is_dir($publicPath)) {
            mkdir($publicPath, 0777, true);
        }

        if (!is_dir($internalPath)) {
            mkdir($internalPath, 0777, true);
        }
    }

    public function removeImages($path)
    {
        $files = glob($path . '/*'); // get all file names

        foreach ($files as $file) { // iterate files
            if (is_file($file))
                unlink($file); // delete file
        }

        rmdir($path);
    }

    public function getImageUrl($collections)
    {
        $host = request()->getHttpHost();

        foreach ($collections as $collection) {
            if ($collection->public_image) {

                $images = $this->imageRepo->getModelByFields([
                    ['id', 'IN', $collection->public_image]
                ]);

                if (count($images) > 0) {
                    $list = array_column($images->toArray(), 'main');
                    foreach ($list as $index => $url) {
                        if (!$this->S3Helper->checkS3Path($url)) {
                            $list[$index] = $host . $url;
                        }
                    }

                    $collection->public_image = json_encode(implode(', ', $list));
                }
            }

            if ($collection->internal_image) {
                $images = $this->imageRepo->getModelByFields([
                    ['id', 'IN', $collection->internal_image]
                ]);

                if (count($images) > 0) {
                    $list = array_column($images->toArray(), 'main');
                    $collection->internal_image = json_encode(implode(', ', $list));
                }
            }
        }

        return $collections;
    }
}
