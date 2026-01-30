import axios, { AxiosInstance } from "axios";
import { PhpAuthService } from "./php-auth.service";

export interface PhpHouseListQuery {
  page?: number;
  size?: number;
  project_id?: number;
  [key: string]: any;
}

export interface PhpHouseListResponse {
  data: any[];
  total: number;
}

/**
 * Call PHP Laravel House API (getHouse, getHouseDetail) with auth.
 */
export class PhpPropertiesService {
  private auth: PhpAuthService;
  private client: AxiosInstance;

  constructor() {
    this.auth = new PhpAuthService();
    this.client = axios.create({
      baseURL: process.env.PHP_API_BASE_URL || "",
      headers: { "Content-Type": "application/json" },
      timeout: 30000,
    });
  }

  private async request<T>(
    token: string,
    method: string,
    url: string,
    params?: any,
    data?: any
  ): Promise<T> {
    const config: any = {
      method,
      url,
      headers: { Authorization: `Bearer ${token}` },
    };
    if (params) config.params = params;
    if (data) config.data = data;
    const res = await this.client.request<T>(config);
    return res.data;
  }

  /**
   * Get list of houses from PHP API (getHouse).
   * Response shape: { data: houses[], total } (PHP success() wrapper may differ)
   */
  async getHouseList(
    accessToken: string | null,
    query: PhpHouseListQuery = {}
  ): Promise<PhpHouseListResponse> {
    const token = await this.auth.getAccessToken(accessToken);
    const url = process.env.PHP_HOUSES_LIST_URL || "/api/house";
    const raw = await this.request<any>(token, "POST", url, query, {
      fetchType: "publicList",
    });
    const inner = raw?.data;
    const list = inner?.public?.data ?? [];
    const total = inner?.public?.total ?? 0;
    return { data: list, total };
  }

  /**
   * Get single house detail from PHP API (getHouseDetail).
   */
  async getHouseDetail(
    accessToken: string | null,
    houseId: number
  ): Promise<any> {
    const token = await this.auth.getAccessToken(accessToken);
    const url = process.env.PHP_HOUSE_DETAIL_URL || "/api/house/detail";
    const raw = await this.request<any>(token, "GET", url, {
      house_id: houseId,
    });
    console.log("raw getHouseDetail", raw);
    return raw?.data ?? raw;
  }
}
