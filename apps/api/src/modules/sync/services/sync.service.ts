import { Property } from "../entities/property.entity";
import { WordPressPost } from "../interfaces/wordpress-post.interface";
import axios, { AxiosInstance } from "axios";
import FormData from "form-data";
import { SyncJobService } from "./sync-job.service";

/** WordPress taxonomy term from GET /wp/v2/{taxonomy} */
interface WPTaxonomyTerm {
  id: number;
  name: string;
  slug: string;
  taxonomy: string;
}

export class SyncService {
  private wpApiClient: AxiosInstance;
  private syncJobService: SyncJobService;
  /** Cache: taxonomy slug -> (term name or slug -> WP term id) */
  private taxonomyTermCache = new Map<string, Map<string, number>>();

  constructor() {
    const wpBaseUrl =
      process.env.WP_API_BASE_URL || "https://thinhgialand.com/wp-json/wp/v2";
    const wpUsername = process.env.WP_API_USERNAME || "";
    const wpPassword = process.env.WP_API_PASSWORD || "";

    console.log("wpUsername", wpUsername);
    console.log("wpPassword", wpPassword);
    // WordPress REST API requires Basic Auth
    // const auth = Buffer.from(`${wpUsername}:${wpPassword}`).toString("base64");

    this.wpApiClient = axios.create({
      baseURL: wpBaseUrl,
      auth: {
        username: wpUsername,
        password: wpPassword,
      },
      headers: {
        "Content-Type": "application/json",
        // Authorization: `Basic ${auth}`,
      },
    });

    this.syncJobService = new SyncJobService();
  }

  /**
   * Fetch all terms for a taxonomy from WordPress and cache by name/slug.
   * Uses cache on subsequent calls to avoid hitting WP API every time.
   */
  private async ensureTaxonomyTerms(
    taxonomy: string
  ): Promise<Map<string, number>> {
    const cached = this.taxonomyTermCache.get(taxonomy);
    if (cached) return cached;

    const nameToId = new Map<string, number>();
    let page = 1;
    const perPage = 100;

    while (true) {
      const res = await this.wpApiClient.get<WPTaxonomyTerm[]>(`/${taxonomy}`, {
        params: { per_page: perPage, page },
      });
      const terms = Array.isArray(res.data) ? res.data : [];
      for (const t of terms) {
        nameToId.set(t.name, t.id);
        nameToId.set(t.slug, t.id);
        // Normalized slug form for flexible matching (e.g. "Đông" -> "dong")
        const normalized = this.normalizeTermNameForLookup(t.name);
        if (normalized) nameToId.set(normalized, t.id);
      }
      if (terms.length < perPage) break;
      page++;
    }

    this.taxonomyTermCache.set(taxonomy, nameToId);
    return nameToId;
  }

  /**
   * Normalize a term name for lookup (slug-like: lowercase, no diacritics).
   */
  private normalizeTermNameForLookup(name: string): string {
    return name
      .trim()
      .toLowerCase()
      .normalize("NFD")
      .replace(/[\u0300-\u036f]/g, "")
      .replace(/\s+/g, "-")
      .replace(/[^a-z0-9-]/g, "");
  }

  /**
   * Resolve direction name to WP term ID from a taxonomy map.
   */
  private resolveTermId(
    termMap: Map<string, number>,
    directionName: string
  ): number | undefined {
    const trimmed = directionName?.trim();
    if (!trimmed) return undefined;
    return (
      termMap.get(trimmed) ??
      termMap.get(this.normalizeTermNameForLookup(trimmed))
    );
  }

  /**
   * Transform PHP House Property to WordPress Post format
   */
  transformPropertyToWordPress(property: Property): WordPressPost {
    const wpPost: WordPressPost = {
      slug: property.slug || this.generateSlug(property.title || "property"),
      status:
        this.mapStatus(
          property.status,
          property.web,
          property.public_approval
        ) || "draft",
      title: {
        raw: property.title || "",
      },
      content: {
        raw: this.buildContent(property),
      },
    };

    // Set dates if available
    if (property.created_at) {
      const date = new Date(Number(property.created_at) * 1000);
      wpPost.date = date.toISOString();
      wpPost.date_gmt = date.toISOString();
    } else {
      // Default to current time if not provided
      const now = new Date();
      wpPost.date = now.toISOString();
      wpPost.date_gmt = now.toISOString();
    }

    // Map taxonomy IDs based on PHP House model structure

    // Property type (loai-bds)
    if (property.property_type) {
      wpPost["loai-bds"] = [property.property_type];
    } else if (property.property_type_obj?.id) {
      wpPost["loai-bds"] = [property.property_type_obj.id];
    }

    // House type (loai-nha)
    if (property.house_type) {
      wpPost["loai-nha"] = [property.house_type];
    } else if (property.house_type_obj?.id) {
      wpPost["loai-nha"] = [property.house_type_obj.id];
    }

    // Floors (so-tang)
    if (property.floors !== undefined && property.floors !== null) {
      // If floors is already a taxonomy term ID, use it directly
      // Otherwise, you may need to map the number to a term ID
      // For now, assuming it's already a term ID
      wpPost["so-tang"] = [property.floors];
    }

    // Province/City (tinh-thanh)
    if (property.city_id) {
      wpPost["tinh-thanh"] = [property.city_id];
    } else if (property.city?.id) {
      wpPost["tinh-thanh"] = [property.city.id];
    } else if (property.province_obj?.id) {
      wpPost["tinh-thanh"] = [property.province_obj.id];
    }

    // District (quan-huyen)
    if (property.district_obj?.id) {
      wpPost["quan-huyen"] = [property.district_obj.id];
    }

    // Ward/Village (phuong-xa) - may need mapping if village is a string
    // Assuming you have a mapping or it's an ID

    // Area (dien-tich-bds) - map area to taxonomy term ID
    // Note: You may need to calculate the range based on area value
    // For now, assuming area_range_id would be provided or calculated elsewhere
    // This might need a helper function to map area to range

    // Price range (muc-gia-bds) - map into_money to taxonomy term ID
    // Similar to area, this may need calculation

    // House directions (huong-nha) - map PHP direction name -> WP taxonomy term ID via cache
    const huongNhaMap = this.taxonomyTermCache.get("huong-nha");
    if (
      property.house_directions &&
      property.house_directions.length > 0 &&
      huongNhaMap
    ) {
      const wpIds = property.house_directions
        .map((hd) => this.resolveTermId(huongNhaMap, hd.direction))
        .filter((id): id is number => id != null);
      const uniqueIds = [...new Set(wpIds)];
      if (uniqueIds.length > 0) wpPost["huong-nha"] = uniqueIds;
    }

    // Balcony directions (huong-ban-cong) - map PHP direction name -> WP term ID via cache
    const huongBanCongMap = this.taxonomyTermCache.get("huong-ban-cong");
    if (
      property.house_balcony_directions &&
      property.house_balcony_directions.length > 0 &&
      huongBanCongMap
    ) {
      const wpIds = property.house_balcony_directions
        .map((hd) => this.resolveTermId(huongBanCongMap, hd.direction))
        .filter((id): id is number => id != null);
      const uniqueIds = [...new Set(wpIds)];
      if (uniqueIds.length > 0) wpPost["huong-ban-cong"] = uniqueIds;
    }

    // Ownership/Legal document (giay-to-phap-ly)
    if (property.ownership !== undefined && property.ownership !== null) {
      wpPost["giay-to-phap-ly"] = [property.ownership];
    }

    // Tags (tien-ich or bat-dong-san) - from tags array
    if (property.tags && property.tags.length > 0) {
      // Assuming tags map to tien-ich (amenities)
      wpPost["tien-ich"] = property.tags;
    }

    // Interior (noi-that-bds) - may need to be mapped from house features
    // Could be calculated from dinning_room, kitchen, terrace, etc.
    // This might need additional mapping logic

    // Set meta fields with property details
    wpPost.meta = {
      _acf_changed: true,
      // Price information
      gia: property.into_money,
      area: property.area,
      floor_area: property.floor_area,
      width: property.width,
      length: property.length,
      // Address details
      address: property.house_address || property.house_number,
      village: property.village,
      district: property.district,
      province: property.province,
      // Property features
      bedrooms: property.number_bedroom,
      bathrooms: property.number_wc,
      dinning_room: property.dinning_room,
      kitchen: property.kitchen,
      terrace: property.terrace,
      car_parking: property.car_parking,
      floors: property.floors,
      // Additional info
      sign: property.sign,
      project_id: property.project_id,
      customer_id: property.customer_id,
      key_word: property.key_word,
    };

    return wpPost;
  }

  /**
   * Build content from multiple description fields
   */
  private buildContent(property: Property): string {
    const parts: string[] = [];

    // Main description
    if (property.description) {
      parts.push(`<p>${this.escapeHtml(property.description)}</p>`);
    }

    // Additional descriptions
    if (property.descriptions) {
      parts.push(`<div class="descriptions">${property.descriptions}</div>`);
    }

    // Initial description
    if (property.initialDescription) {
      parts.push(
        `<div class="initial-description">${property.initialDescription}</div>`
      );
    }

    // Internal description (might want to conditionally include)
    if (property.internalDescription && property.web) {
      parts.push(
        `<div class="internal-description">${property.internalDescription}</div>`
      );
    }

    // Property details as structured content
    const details: string[] = [];
    if (property.area)
      details.push(`<strong>Diện tích:</strong> ${property.area} m²`);
    if (property.floor_area)
      details.push(`<strong>Diện tích sàn:</strong> ${property.floor_area} m²`);
    if (property.into_money)
      details.push(
        `<strong>Giá:</strong> ${this.formatPrice(property.into_money)}`
      );
    if (property.number_bedroom)
      details.push(`<strong>Phòng ngủ:</strong> ${property.number_bedroom}`);
    if (property.number_wc)
      details.push(`<strong>Phòng tắm:</strong> ${property.number_wc}`);
    if (property.floors)
      details.push(`<strong>Số tầng:</strong> ${property.floors}`);
    if (property.house_address)
      details.push(`<strong>Địa chỉ:</strong> ${property.house_address}`);

    if (details.length > 0) {
      parts.unshift(
        `<div class="property-details"><ul><li>${details.join("</li><li>")}</li></ul></div>`
      );
    }

    return parts.join("\n") || "";
  }

  /**
   * Escape HTML for safe display
   */
  private escapeHtml(text: string): string {
    const map: Record<string, string> = {
      "&": "&amp;",
      "<": "&lt;",
      ">": "&gt;",
      '"': "&quot;",
      "'": "&#039;",
    };
    return text.replace(/[&<>"']/g, (m) => map[m]);
  }

  /**
   * Format price with Vietnamese currency
   */
  private formatPrice(price: number): string {
    return new Intl.NumberFormat("vi-VN", {
      style: "currency",
      currency: "VND",
    }).format(price);
  }

  /**
   * Collect all image URLs from property (public_image first, then internal_image)
   */
  private collectAllImageUrls(property: Property): string[] {
    const urls: string[] = [];
    const sources: (string | string[] | undefined)[] = [
      property.public_image,
      property.internal_image,
    ];

    for (const src of sources) {
      if (!src) continue;
      const arr = Array.isArray(src) ? src : [src];
      for (const v of arr) {
        if (typeof v === "string" && v.startsWith("http")) {
          urls.push(v);
        }
      }
    }
    return urls;
  }

  /**
   * Upload image to WordPress Media API and return media ID.
   * Supports optional title, alt_text, caption per WP media spec.
   */
  async uploadImageToWordPress(
    imageUrl: string,
    options?: { title?: string; alt_text?: string; caption?: string }
  ): Promise<number | null> {
    try {
      if (!imageUrl || !imageUrl.startsWith("http")) return null;

      const imageResponse = await axios.get(imageUrl, {
        responseType: "arraybuffer",
        timeout: 30000,
      });

      const filename = imageUrl.split("/").pop()?.split("?")[0] || "image.jpg";
      const imageBuffer = Buffer.from(imageResponse.data);
      const contentType = imageResponse.headers["content-type"] || "image/jpeg";

      const formData = new FormData();
      formData.append("file", imageBuffer, {
        filename,
        contentType,
      });
      formData.append("title", options?.title ?? filename);
      if (options?.alt_text) formData.append("alt_text", options.alt_text);
      if (options?.caption) formData.append("caption", options.caption);

      const uploadResponse = await this.wpApiClient.post("/media", formData, {
        headers: { ...formData.getHeaders() },
        maxContentLength: Infinity,
        maxBodyLength: Infinity,
      });

      return uploadResponse.data?.id ?? null;
    } catch (error) {
      console.error("Error uploading image to WordPress:", imageUrl, error);
      if (axios.isAxiosError(error)) {
        console.error("WordPress API error details:", error.response?.data);
      }
      return null;
    }
  }

  /**
   * Attach media to a post by setting the media's post ID (per WP media spec).
   */
  async attachMediaToPost(mediaId: number, postId: number): Promise<void> {
    try {
      await this.wpApiClient.patch(`/media/${mediaId}`, { post: postId });
    } catch (error) {
      console.error(
        `Error attaching media ${mediaId} to post ${postId}:`,
        error
      );
      if (axios.isAxiosError(error)) {
        console.error("WordPress API error:", error.response?.data);
      }
      throw error;
    }
  }

  /**
   * Upload all property images to WordPress and optionally attach them to a post.
   * Returns { mediaIds, featuredId } where featuredId is the first successful upload.
   */
  async uploadAllPropertyMedia(
    property: Property
  ): Promise<{ mediaIds: number[]; featuredId: number | null }> {
    const urls = this.collectAllImageUrls(property);
    const mediaIds: number[] = [];

    for (const url of urls) {
      const id = await this.uploadImageToWordPress(url, {
        title: property.title,
        alt_text: property.title,
      });
      if (id) mediaIds.push(id);
    }

    return {
      mediaIds,
      featuredId: mediaIds[0] ?? null,
    };
  }

  /**
   * Sync a single property to WordPress
   * Uploads all images (public_image, internal_image) as media, sets featured_media,
   * creates the BDS post, then attaches all media to the post via the media `post` field.
   */
  async syncPropertyToWordPress(property: Property): Promise<any> {
    if (!property.title) {
      throw new Error("Property title is required");
    }

    // Ensure taxonomy caches are loaded so transform can map direction names -> WP term IDs
    await this.ensureTaxonomyTerms("huong-nha");
    await this.ensureTaxonomyTerms("huong-ban-cong");

    const wpPost = this.transformPropertyToWordPress(property);

    // 1) Upload all property images; first one becomes featured_media
    const { mediaIds, featuredId } =
      await this.uploadAllPropertyMedia(property);
    if (featuredId) {
      wpPost.featured_media = featuredId;
    }

    const postType = process.env.WP_POST_TYPE || "bds";

    // 2) Create the BDS post
    const response = await this.wpApiClient.post(`/${postType}`, wpPost);
    const created = response.data;
    const postId = created?.id;

    // 3) Attach all uploaded media to the post (WP media `post` field)
    if (postId && mediaIds.length > 0) {
      for (const mediaId of mediaIds) {
        try {
          await this.attachMediaToPost(mediaId, postId);
        } catch (e) {
          console.warn(
            `Could not attach media ${mediaId} to post ${postId}, skipping.`,
            e
          );
        }
      }
    }

    return created;
  }

  /**
   * Process a sync job asynchronously
   * Updates job status as it progresses
   */
  async processSyncJob(jobId: string): Promise<void> {
    try {
      // Mark job as processing
      await this.syncJobService.markAsProcessing(jobId);
      const job = await this.syncJobService.getJobById(jobId);
      if (!job) {
        throw new Error(`Job ${jobId} not found`);
      }

      if (job.type === "single_property") {
        // Process single property
        const property = job.payload as Property;
        try {
          const result = await this.syncPropertyToWordPress(property);
          await this.syncJobService.markAsSuccess(jobId, result, 1);
        } catch (error) {
          const errorMessage =
            error instanceof Error ? error.message : "Unknown error";
          await this.syncJobService.markAsFailed(jobId, errorMessage);
        }
      } else if (job.type === "multiple_properties") {
        // Process multiple properties
        const properties = (job.payload as { properties: Property[] })
          .properties;
        let successCount = 0;
        let failedCount = 0;
        const errors: Array<{ propertyId?: number; error: string }> = [];
        const results: any[] = [];

        for (const property of properties) {
          try {
            const result = await this.syncPropertyToWordPress(property);
            successCount++;
            results.push({ propertyId: property.id, success: true, result });
            // Update progress periodically
            await this.syncJobService.updateProgress(
              jobId,
              successCount,
              failedCount,
              results
            );
          } catch (error) {
            failedCount++;
            const errorMessage =
              error instanceof Error ? error.message : "Unknown error";
            errors.push({
              propertyId: property.id,
              error: errorMessage,
            });
            console.error("Error syncing property to WordPress:", error);
            results.push({
              propertyId: property.id,
              success: false,
              error: errorMessage,
            });
            // Update progress periodically
            await this.syncJobService.updateProgress(
              jobId,
              successCount,
              failedCount,
              results
            );
          }
        }

        // Mark job as completed
        if (failedCount === 0) {
          await this.syncJobService.markAsSuccess(
            jobId,
            { results, errors },
            successCount
          );
        } else if (successCount === 0) {
          await this.syncJobService.markAsFailed(
            jobId,
            `All properties failed. Errors: ${JSON.stringify(errors)}`
          );
        } else {
          // Partial success - mark as success but include errors
          const job = await this.syncJobService.getJobById(jobId);
          if (job) {
            await this.syncJobService.markAsSuccess(
              jobId,
              { results, errors },
              successCount
            );
            // Update failed count
            await this.syncJobService.updateProgress(
              jobId,
              successCount,
              failedCount,
              results
            );
          }
        }
      }
    } catch (error) {
      console.error(`Error processing sync job ${jobId}:`, error);
      const errorMessage =
        error instanceof Error ? error.message : "Unknown error";
      await this.syncJobService.markAsFailed(jobId, errorMessage);
    }
  }

  /**
   * Generate slug from title
   */
  private generateSlug(title: string): string {
    return title
      .toLowerCase()
      .normalize("NFD")
      .replace(/[\u0300-\u036f]/g, "") // Remove diacritics
      .replace(/[^a-z0-9]+/g, "-")
      .replace(/^-+|-+$/g, "");
  }

  /**
   * Map PHP House status to WordPress status
   */
  private mapStatus(
    status?: string,
    web?: number,
    publicApproval?: number
  ): WordPressPost["status"] {
    // If explicitly set to web and approved, publish
    if (web === 1 && publicApproval === 1) {
      return "publish";
    }

    // Map status string
    if (status) {
      const statusMap: Record<string, WordPressPost["status"]> = {
        active: "publish",
        published: "publish",
        draft: "draft",
        pending: "pending",
        private: "private",
        inactive: "draft",
        approved: "publish",
      };
      return statusMap[status.toLowerCase()] || "draft";
    }

    // Default to draft if not specified
    return "draft";
  }
}
