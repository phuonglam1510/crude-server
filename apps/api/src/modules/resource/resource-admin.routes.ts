import { Router } from 'express';
import { ResourceController } from './controllers/resource.controller';
import { authenticate } from '../common/guards/auth.guard';

const router = Router();
const resourceController = new ResourceController();

// All admin routes require authentication
router.use(authenticate);

router.post('/', resourceController.create);
router.get('/', resourceController.list);
router.get('/:id', resourceController.getById);
router.put('/:id', resourceController.update);
router.delete('/:id', resourceController.delete);

export default router;

