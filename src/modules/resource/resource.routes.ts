import { Router } from 'express';
import { ResourceController } from './controllers/resource.controller';

const router = Router();
const resourceController = new ResourceController();

router.post('/', resourceController.create);
router.get('/', resourceController.list);
router.get('/:id', resourceController.getById);
router.put('/:id', resourceController.update);
router.delete('/:id', resourceController.delete);

export default router;