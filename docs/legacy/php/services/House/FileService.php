<?php


namespace App\Services\House;

use App\Helpers\ImageHelper;
use App\Helpers\S3Helper;
use App\Repositories\House\HouseRepository;
use App\Repositories\House\FileRepository;
use App\Http\Traits\CustomResponse;

class FileService
{
    use CustomResponse;
    public $fileRepo;
    public $imageHelper;
    public $S3Helper;
    public $houseRepo;
    protected $houseZip = '/zip/houses/';

    public function __construct(
        FileRepository $fileRepository,
        ImageHelper $imageHelper,
        S3Helper $s3Helper,
        HouseRepository $houseRepository
    ) {
        $this->fileRepo = $fileRepository;
        $this->imageHelper = $imageHelper;
        $this->S3Helper = $s3Helper;
        $this->houseRepo = $houseRepository;
    }

    /**
     * Upload file with mime
     * @param $file
     * @param $rotate
     * @return mixed|null
     */
    public function addFile($file)
    {
        $fileName = $file->getClientOriginalName();
        $mimeType = $file->getMimeType();
        $fileSize = $file->getSize();
        $folderPath = "uploads/files/" . date('Ym');
        $file->move($folderPath, $fileName);

        $path = $folderPath . '/' . $fileName;


        $data['url'] = $this->S3Helper->uploadImage(public_path($path), $path, $mimeType);
        $data['size'] = $fileSize;
        $data['type'] = $mimeType;
        $data['name'] = $fileName;

        if (!$data['url']) {
            return null;
        }

        $file = $this->fileRepo->create($data);
        // Remove main
        if (file_exists(public_path($path))) {
            unlink(public_path($path));
        }


        return [
            'file' => $file,
            'url' => $path,
        ];


        return null;
    }
}
