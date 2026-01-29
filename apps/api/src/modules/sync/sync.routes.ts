import { Router } from 'express';
import { SyncController } from './controllers/sync.controller';

const router = Router();
const syncController = new SyncController();

// Sync a single property (receives Property object in request body from PHP, processes asynchronously via queue)
router.post('/property', syncController.syncProperty);

// Sync multiple properties (receives { properties: Property[] } in request body from PHP, processes asynchronously via queue)
router.post('/properties', syncController.syncProperties);

// Get sync job by ID (includes queue state)
router.get('/jobs/:id', syncController.getJob);

// Get all sync jobs with optional filters
router.get('/jobs', syncController.getJobs);

// Retry a failed job
router.post('/jobs/:id/retry', syncController.retryJob);

// Get queue statistics
router.get('/queue/stats', syncController.getQueueStats);

export default router;
