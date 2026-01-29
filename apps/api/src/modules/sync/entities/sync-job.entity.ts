import { Entity, PrimaryGeneratedColumn, Column, CreateDateColumn, UpdateDateColumn } from 'typeorm';

export enum SyncJobStatus {
    PENDING = 'pending',
    PROCESSING = 'processing',
    SUCCESS = 'success',
    FAILED = 'failed',
}

export enum SyncJobType {
    SINGLE_PROPERTY = 'single_property',
    MULTIPLE_PROPERTIES = 'multiple_properties',
}

@Entity('sync_jobs')
export class SyncJob {
    @PrimaryGeneratedColumn('uuid')
    id: string;

    @Column({
        type: 'enum',
        enum: SyncJobType,
    })
    type: SyncJobType;

    @Column({
        type: 'enum',
        enum: SyncJobStatus,
        default: SyncJobStatus.PENDING,
    })
    status: SyncJobStatus;

    @Column({ type: 'jsonb', nullable: true })
    payload: any; // Property or Property[] data

    @Column({ type: 'jsonb', nullable: true })
    result: any; // WordPress API response

    @Column({ type: 'text', nullable: true })
    error: string; // Error message if failed

    @Column({ type: 'integer', default: 0 })
    total_items: number; // Total number of properties to sync

    @Column({ type: 'integer', default: 0 })
    success_count: number; // Number of successfully synced items

    @Column({ type: 'integer', default: 0 })
    failed_count: number; // Number of failed items

    @Column({ type: 'integer', default: 0 })
    retry_count: number; // Number of retry attempts

    @Column({ type: 'integer', default: 3 })
    max_retries: number; // Maximum number of retries

    @Column({ type: 'timestamp', nullable: true })
    started_at: Date | null;

    @Column({ type: 'timestamp', nullable: true })
    completed_at: Date | null;

    @CreateDateColumn()
    created_at: Date;

    @UpdateDateColumn()
    updated_at: Date;
}
