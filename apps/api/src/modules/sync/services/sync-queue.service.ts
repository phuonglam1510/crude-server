import { Queue, Worker, Job } from 'bullmq';
import { SyncService } from './sync.service';
import { SyncJobService } from './sync-job.service';

export class SyncQueueService {
    private queue: Queue;
    private worker: Worker;

    constructor() {
        const redisConnection = {
            host: process.env.REDIS_HOST || 'localhost',
            port: parseInt(process.env.REDIS_PORT || '6379'),
            maxRetriesPerRequest: null,
        };

        // Create queue
        this.queue = new Queue('sync-jobs', {
            connection: redisConnection,
            defaultJobOptions: {
                attempts: 3,
                backoff: {
                    type: 'exponential',
                    delay: 2000,
                },
                removeOnComplete: {
                    age: 86400, // Keep completed jobs for 24 hours
                    count: 1000, // Keep max 1000 completed jobs
                },
                removeOnFail: {
                    age: 7 * 86400, // Keep failed jobs for 7 days
                },
            },
        });

        // Create worker
        this.worker = new Worker(
            'sync-jobs',
            async (job: Job) => {
                const { jobId } = job.data;

                // Initialize services for each job (fresh instances)
                const syncService = new SyncService();

                await syncService.processSyncJob(jobId);
            },
            {
                connection: redisConnection,
                concurrency: 5, // Process 5 jobs concurrently
                limiter: {
                    max: 10, // Max 10 jobs
                    duration: 1000, // Per second
                },
            }
        );

        // Worker event listeners
        this.worker.on('completed', (job) => {
            console.log(`[Queue] Job ${job.id} completed`);
        });

        this.worker.on('failed', (job, err) => {
            console.error(`[Queue] Job ${job?.id} failed:`, err);
        });

        this.worker.on('error', (err) => {
            console.error('[Queue] Worker error:', err);
        });

        this.worker.on('progress', (job, progress) => {
            console.log(`[Queue] Job ${job.id} progress: ${progress}%`);
        });
    }

    /**
     * Add job to queue
     */
    async addJob(jobId: string, priority: number = 0): Promise<void> {
        await this.queue.add(
            'sync-property',
            { jobId },
            {
                priority,
                jobId, // Use sync job ID as BullMQ job ID
            }
        );
    }

    /**
     * Get queue statistics
     */
    async getQueueStats() {
        const [waiting, active, completed, failed, delayed] = await Promise.all([
            this.queue.getWaitingCount(),
            this.queue.getActiveCount(),
            this.queue.getCompletedCount(),
            this.queue.getFailedCount(),
            this.queue.getDelayedCount(),
        ]);

        return {
            waiting,
            active,
            completed,
            failed,
            delayed,
            total: waiting + active + completed + failed + delayed,
        };
    }

    /**
     * Get job by ID
     */
    async getJob(jobId: string): Promise<Job | undefined> {
        return this.queue.getJob(jobId);
    }

    /**
     * Get job state
     */
    async getJobState(jobId: string) {
        const job = await this.queue.getJob(jobId);
        if (!job) {
            return null;
        }

        const state = await job.getState();
        const progress = job.progress;
        const returnvalue = job.returnvalue;
        const failedReason = job.failedReason;

        return {
            id: job.id,
            name: job.name,
            data: job.data,
            state,
            progress,
            returnvalue,
            failedReason,
            attemptsMade: job.attemptsMade,
            timestamp: job.timestamp,
            processedOn: job.processedOn,
            finishedOn: job.finishedOn,
        };
    }

    /**
     * Retry failed job
     */
    async retryJob(jobId: string): Promise<void> {
        const job = await this.queue.getJob(jobId);
        if (job) {
            await job.retry();
        } else {
            throw new Error(`Job ${jobId} not found`);
        }
    }

    /**
     * Remove job from queue
     */
    async removeJob(jobId: string): Promise<void> {
        const job = await this.queue.getJob(jobId);
        if (job) {
            await job.remove();
        }
    }

    /**
     * Pause queue
     */
    async pause(): Promise<void> {
        await this.queue.pause();
    }

    /**
     * Resume queue
     */
    async resume(): Promise<void> {
        await this.queue.resume();
    }

    /**
     * Close connections
     */
    async close(): Promise<void> {
        await this.worker.close();
        await this.queue.close();
    }
}
