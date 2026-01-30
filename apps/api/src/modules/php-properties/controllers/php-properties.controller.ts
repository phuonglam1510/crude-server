import { Request, Response } from 'express';
import { PhpAuthService } from '../services/php-auth.service';
import { PhpPropertiesService } from '../services/php-properties.service';
import { mapPhpHouseToProperty } from '../mappers/php-house-to-property.mapper';
import { SyncJobService } from '../../sync/services/sync-job.service';
import { SyncQueueService } from '../../sync/services/sync-queue.service';

const authService = new PhpAuthService();
const phpPropertiesService = new PhpPropertiesService();
const syncJobService = new SyncJobService();
const syncQueueService = new SyncQueueService();

export class PhpPropertiesController {
    /**
     * Login to PHP API and get access token.
     * POST /api/php-properties/auth/login
     * Body: { email, password }
     */
    async login(req: Request, res: Response): Promise<void> {
        try {
            const { email, password } = req.body || {};
            if (!email || !password) {
                res.status(400).json({ message: 'email and password are required' });
                return;
            }
            const result = await authService.login(email, password);
            res.status(200).json(result);
        } catch (error: any) {
            const status = error.response?.status ?? 500;
            const message = error.response?.data?.message ?? error.message ?? 'PHP login failed';
            res.status(status).json({ message });
        }
    }

    /**
     * Get list of properties (houses) from PHP API.
     * GET /api/php-properties?page=1&size=20&...
     * Header: Authorization: Bearer <php_token> (or use server-side PHP_AUTH_EMAIL/PHP_AUTH_PASSWORD)
     */
    async getList(req: Request, res: Response): Promise<void> {
        try {
            const token = PhpAuthService.bearerFromHeader(req.headers.authorization);
            const query = {
                page: req.query.page ? Number(req.query.page) : undefined,
                size: req.query.size ? Number(req.query.size) : undefined,
                ...req.query,
            };
            const { data, total } = await phpPropertiesService.getHouseList(token, query);
            res.status(200).json({ data, total });
        } catch (error: any) {
            const status = error.response?.status ?? 500;
            const message = error.response?.data?.message ?? error.message ?? 'Failed to fetch properties from PHP';
            res.status(status).json({ message });
        }
    }

    /**
     * Get single property detail from PHP API.
     * GET /api/php-properties/:id
     * Header: Authorization: Bearer <php_token>
     */
    async getDetail(req: Request, res: Response): Promise<void> {
        try {
            const id = Number(req.params.id);
            if (!id || Number.isNaN(id)) {
                res.status(400).json({ message: 'Invalid property id' });
                return;
            }
            const token = PhpAuthService.bearerFromHeader(req.headers.authorization);
            const house = await phpPropertiesService.getHouseDetail(token, id);
            const property = mapPhpHouseToProperty(house);
            res.status(200).json(property);
        } catch (error: any) {
            const status = error.response?.status ?? 500;
            const message = error.response?.data?.message ?? error.message ?? 'Failed to fetch property detail';
            res.status(status).json({ message });
        }
    }

    /**
     * Sync selected properties from PHP: fetch full details from PHP then queue sync job.
     * POST /api/php-properties/sync
     * Body: { house_ids: number[] }
     * Header: Authorization: Bearer <php_token> (or server-side credentials)
     */
    async syncSelected(req: Request, res: Response): Promise<void> {
        try {
            const { house_ids } = req.body || {};
            if (!Array.isArray(house_ids) || house_ids.length === 0) {
                res.status(400).json({ message: 'house_ids must be a non-empty array of house IDs' });
                return;
            }
            const token = PhpAuthService.bearerFromHeader(req.headers.authorization);
            const properties: any[] = [];
            for (const id of house_ids) {
                const houseId = Number(id);
                if (Number.isNaN(houseId)) continue;
                try {
                    const house = await phpPropertiesService.getHouseDetail(token, houseId);
                    properties.push(mapPhpHouseToProperty(house));
                } catch (e) {
                    console.warn(`Failed to fetch PHP house ${houseId}:`, e);
                }
            }
            if (properties.length === 0) {
                res.status(400).json({ message: 'Could not fetch any property details from PHP' });
                return;
            }
            const job = await syncJobService.createMultiplePropertiesJob(properties);
            await syncQueueService.addJob(job.id);
            res.status(202).json({
                message: 'Sync job created and queued',
                job_id: job.id,
                status: job.status,
                type: job.type,
                total_items: job.total_items,
            });
        } catch (error: any) {
            const status = error.response?.status ?? 500;
            const message = error.response?.data?.message ?? error.message ?? 'Sync from PHP failed';
            res.status(status).json({ message });
        }
    }
}
