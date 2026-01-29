import { Repository } from 'typeorm';
import { AppDataSource } from '../../../configs/ormconfig';
import { SyncJob, SyncJobStatus, SyncJobType } from '../entities/sync-job.entity';
import { Property } from '../entities/property.entity';

export class SyncJobService {
    private syncJobRepository: Repository<SyncJob>;

    constructor() {
        this.syncJobRepository = AppDataSource.getRepository(SyncJob);
    }

    /**
     * Create a new sync job for a single property
     */
    async createSinglePropertyJob(property: Property): Promise<SyncJob> {
        const job = this.syncJobRepository.create({
            type: SyncJobType.SINGLE_PROPERTY,
            status: SyncJobStatus.PENDING,
            payload: property,
            total_items: 1,
        });
        return this.syncJobRepository.save(job);
    }

    /**
     * Create a new sync job for multiple properties
     */
    async createMultiplePropertiesJob(properties: Property[]): Promise<SyncJob> {
        const job = this.syncJobRepository.create({
            type: SyncJobType.MULTIPLE_PROPERTIES,
            status: SyncJobStatus.PENDING,
            payload: { properties },
            total_items: properties.length,
        });
        return this.syncJobRepository.save(job);
    }

    /**
     * Update job status to processing
     */
    async markAsProcessing(jobId: string): Promise<void> {
        await this.syncJobRepository.update(jobId, {
            status: SyncJobStatus.PROCESSING,
            started_at: new Date(),
        });
    }

    /**
     * Update job status to success
     */
    async markAsSuccess(jobId: string, result: any, successCount: number = 1): Promise<void> {
        await this.syncJobRepository.update(jobId, {
            status: SyncJobStatus.SUCCESS,
            result,
            success_count: successCount,
            completed_at: new Date(),
        });
    }

    /**
     * Update job status to failed
     */
    async markAsFailed(jobId: string, error: string): Promise<void> {
        const job = await this.getJobById(jobId);
        if (!job) return;

        const newRetryCount = job.retry_count + 1;
        const shouldRetry = newRetryCount < job.max_retries;

        await this.syncJobRepository.update(jobId, {
            status: shouldRetry ? SyncJobStatus.PENDING : SyncJobStatus.FAILED,
            error,
            retry_count: newRetryCount,
            failed_count: job.failed_count + 1,
            completed_at: shouldRetry ? null : new Date(),
        });
    }

    /**
     * Update job progress for multiple properties sync
     */
    async updateProgress(
        jobId: string,
        successCount: number,
        failedCount: number,
        partialResults?: any[]
    ): Promise<void> {
        const updateData: any = {
            success_count: successCount,
            failed_count: failedCount,
        };

        if (partialResults !== undefined) {
            updateData.result = partialResults;
        }

        await this.syncJobRepository.update(jobId, updateData);
    }

    /**
     * Get job by ID
     */
    async getJobById(id: string): Promise<SyncJob | null> {
        return this.syncJobRepository.findOne({ where: { id } });
    }

    /**
     * Get all jobs with optional filters
     */
    async getJobs(
        status?: SyncJobStatus,
        type?: SyncJobType,
        limit: number = 50,
        offset: number = 0
    ): Promise<{ jobs: SyncJob[]; total: number }> {
        const queryBuilder = this.syncJobRepository.createQueryBuilder('job');

        if (status) {
            queryBuilder.where('job.status = :status', { status });
        }

        if (type) {
            queryBuilder.andWhere('job.type = :type', { type });
        }

        queryBuilder.orderBy('job.created_at', 'DESC');
        queryBuilder.skip(offset);
        queryBuilder.take(limit);

        const [jobs, total] = await queryBuilder.getManyAndCount();
        return { jobs, total };
    }

    /**
     * Get pending jobs for retry
     */
    async getPendingJobs(limit: number = 10): Promise<SyncJob[]> {
        return this.syncJobRepository.find({
            where: {
                status: SyncJobStatus.PENDING,
            },
            order: {
                created_at: 'ASC',
            },
            take: limit,
        });
    }

    /**
     * Get failed jobs that can be retried
     */
    async getFailedJobsForRetry(limit: number = 10): Promise<SyncJob[]> {
        return this.syncJobRepository
            .createQueryBuilder('job')
            .where('job.status = :status', { status: SyncJobStatus.FAILED })
            .andWhere('job.retry_count < job.max_retries')
            .orderBy('job.updated_at', 'ASC')
            .take(limit)
            .getMany();
    }

    /**
     * Delete old completed jobs (cleanup)
     */
    async deleteOldJobs(olderThanDays: number = 30): Promise<number> {
        const cutoffDate = new Date();
        cutoffDate.setDate(cutoffDate.getDate() - olderThanDays);

        const result = await this.syncJobRepository
            .createQueryBuilder()
            .delete()
            .where('status IN (:...statuses)', {
                statuses: [SyncJobStatus.SUCCESS, SyncJobStatus.FAILED],
            })
            .andWhere('completed_at < :cutoffDate', { cutoffDate })
            .execute();

        return result.affected || 0;
    }
}
