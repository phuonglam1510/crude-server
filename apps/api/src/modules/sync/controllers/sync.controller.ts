import { Request, Response } from 'express';
import { SyncJobService } from '../services/sync-job.service';
import { SyncQueueService } from '../services/sync-queue.service';
import { Property } from '../entities/property.entity';
import { SyncJobStatus, SyncJobType } from '../entities/sync-job.entity';

// Initialize services (singletons)
const syncJobService = new SyncJobService();
const syncQueueService = new SyncQueueService();

export class SyncController {
    /**
     * Sync a single property to WordPress (async via queue)
     * POST /api/sync/property
     * Body: Property object (from PHP Laravel server)
     * Returns: Job ID for tracking
     */
    async syncProperty(req: Request, res: Response): Promise<void> {
        try {
            const property = req.body as Property;

            if (!property || !property.title) {
                res.status(400).json({
                    message: 'Invalid property data. Property object with title is required.',
                    received: property
                });
                return;
            }

            // Create job in database
            const job = await syncJobService.createSinglePropertyJob(property);

            // Add to queue for processing
            await syncQueueService.addJob(job.id);

            res.status(202).json({
                message: 'Sync job created and queued for processing',
                job_id: job.id,
                status: job.status,
                type: job.type,
            });
        } catch (error) {
            console.error('Error creating sync job:', error);
            res.status(500).json({
                message: 'Error creating sync job',
                error: error instanceof Error ? error.message : 'Unknown error',
            });
        }
    }

    /**
     * Sync multiple properties to WordPress (async via queue)
     * POST /api/sync/properties
     * Body: { properties: Property[] } (array of Property objects from PHP Laravel server)
     * Returns: Job ID for tracking
     */
    async syncProperties(req: Request, res: Response): Promise<void> {
        try {
            const { properties } = req.body;

            if (!properties || !Array.isArray(properties)) {
                res.status(400).json({
                    message: 'Invalid request. Expected body: { properties: Property[] }',
                });
                return;
            }

            if (properties.length === 0) {
                res.status(400).json({
                    message: 'Properties array cannot be empty',
                });
                return;
            }

            // Create job in database
            const job = await syncJobService.createMultiplePropertiesJob(properties as Property[]);

            // Add to queue for processing
            await syncQueueService.addJob(job.id);

            res.status(202).json({
                message: 'Sync job created and queued for processing',
                job_id: job.id,
                status: job.status,
                type: job.type,
                total_items: job.total_items,
            });
        } catch (error) {
            console.error('Error creating sync job:', error);
            res.status(500).json({
                message: 'Error creating sync job',
                error: error instanceof Error ? error.message : 'Unknown error',
            });
        }
    }

    /**
     * Get sync job status by ID
     * GET /api/sync/jobs/:id
     */
    async getJob(req: Request, res: Response): Promise<void> {
        try {
            const { id } = req.params;

            // Get job from database
            const job = await syncJobService.getJobById(id);

            if (!job) {
                res.status(404).json({
                    message: 'Job not found',
                });
                return;
            }

            // Get queue job state (if exists)
            const queueJobState = await syncQueueService.getJobState(id);

            res.status(200).json({
                job,
                queue_state: queueJobState,
            });
        } catch (error) {
            console.error('Error getting job:', error);
            res.status(500).json({
                message: 'Error getting job',
                error: error instanceof Error ? error.message : 'Unknown error',
            });
        }
    }

    /**
     * Get all sync jobs with filters
     * GET /api/sync/jobs?status=pending&type=single_property&limit=50&offset=0
     */
    async getJobs(req: Request, res: Response): Promise<void> {
        try {
            const { status, type, limit, offset } = req.query;

            const statusEnum = status ? (status as SyncJobStatus) : undefined;
            const typeEnum = type ? (type as SyncJobType) : undefined;
            const limitNum = limit ? parseInt(limit as string) : 50;
            const offsetNum = offset ? parseInt(offset as string) : 0;

            const { jobs, total } = await syncJobService.getJobs(
                statusEnum,
                typeEnum,
                limitNum,
                offsetNum
            );

            res.status(200).json({
                jobs,
                total,
                limit: limitNum,
                offset: offsetNum,
            });
        } catch (error) {
            console.error('Error getting jobs:', error);
            res.status(500).json({
                message: 'Error getting jobs',
                error: error instanceof Error ? error.message : 'Unknown error',
            });
        }
    }

    /**
     * Get queue statistics
     * GET /api/sync/queue/stats
     */
    async getQueueStats(req: Request, res: Response): Promise<void> {
        try {
            const stats = await syncQueueService.getQueueStats();
            res.status(200).json(stats);
        } catch (error) {
            console.error('Error getting queue stats:', error);
            res.status(500).json({
                message: 'Error getting queue statistics',
                error: error instanceof Error ? error.message : 'Unknown error',
            });
        }
    }

    /**
     * Retry a failed job
     * POST /api/sync/jobs/:id/retry
     */
    async retryJob(req: Request, res: Response): Promise<void> {
        try {
            const { id } = req.params;

            // Check if job exists in database
            const job = await syncJobService.getJobById(id);
            if (!job) {
                res.status(404).json({
                    message: 'Job not found',
                });
                return;
            }

            // Reset job status to pending
            await syncJobService.markAsProcessing(id);

            // Retry in queue
            await syncQueueService.retryJob(id);

            res.status(200).json({
                message: 'Job queued for retry',
                job_id: id,
            });
        } catch (error) {
            console.error('Error retrying job:', error);
            res.status(500).json({
                message: 'Error retrying job',
                error: error instanceof Error ? error.message : 'Unknown error',
            });
        }
    }
}
