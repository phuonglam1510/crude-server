import { Router } from 'express';
import { PhpPropertiesController } from './controllers/php-properties.controller';

const router = Router();
const controller = new PhpPropertiesController();

router.post('/auth/login', controller.login);
router.get('/', controller.getList);
router.get('/:id', controller.getDetail);
router.post('/sync', controller.syncSelected);

export default router;
