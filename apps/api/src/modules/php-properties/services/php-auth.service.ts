import axios, { AxiosInstance } from 'axios';

interface TokenCache {
    token: string;
    expiresAt: number;
}

/**
 * PHP Laravel API auth: get access token via login, cache for reuse.
 * Supports client-provided Bearer token or server-side credentials (env).
 */
export class PhpAuthService {
    private client: AxiosInstance;
    private tokenCache: TokenCache | null = null;
    private cacheTtlMs: number = 55 * 60 * 1000; // 55 minutes

    constructor() {
        const baseURL = process.env.PHP_API_BASE_URL || '';
        this.client = axios.create({
            baseURL,
            headers: { 'Content-Type': 'application/json' },
            timeout: 15000,
        });
    }

    get baseURL(): string {
        return process.env.PHP_API_BASE_URL || '';
    }

    /**
     * Login to PHP API and return token + user.
     * Body: { email, password } or { type: 'email', email, password }
     */
    async login(email: string, password: string): Promise<{ token: string; user?: any }> {
        const loginUrl = process.env.PHP_AUTH_LOGIN_URL || `${this.baseURL}/api/user/signin/email`;
        const body = process.env.PHP_AUTH_USE_TYPE_BODY === 'true'
            ? { type: 'email', email, password }
            : { email, password };

        const { data } = await this.client.post(loginUrl, body);
        if (!data) throw new Error('PHP auth: empty response');

        const token = data.token ?? data.access_token ?? data.data?.token ?? data.data?.access_token;
        if (!token) throw new Error('PHP auth: no token in response');

        this.tokenCache = { token, expiresAt: Date.now() + this.cacheTtlMs };
        return { token, user: data.user ?? data.data?.user };
    }

    /**
     * Get a valid token: use server-side credentials if no token provided.
     */
    async getAccessToken(clientToken?: string | null): Promise<string> {
        if (clientToken) return clientToken;

        if (this.tokenCache && this.tokenCache.expiresAt > Date.now()) {
            return this.tokenCache.token;
        }

        const email = process.env.PHP_AUTH_EMAIL;
        const password = process.env.PHP_AUTH_PASSWORD;
        if (!email || !password) {
            throw new Error('PHP API requires authentication. Provide Authorization: Bearer <token> or set PHP_AUTH_EMAIL and PHP_AUTH_PASSWORD.');
        }

        const { token } = await this.login(email, password);
        return token;
    }

    /**
     * Extract Bearer token from Authorization header.
     */
    static bearerFromHeader(authHeader: string | undefined): string | null {
        if (!authHeader || !authHeader.startsWith('Bearer ')) return null;
        return authHeader.slice(7).trim() || null;
    }
}
