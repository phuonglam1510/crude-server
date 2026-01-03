import { Router } from 'express';
import { ResourceController } from './controllers/resource.controller';

const router = Router();
const resourceController = new ResourceController();

// Public routes - only listing is allowed
router.get('/', resourceController.list);

export default router;