import { Router } from 'express';
import { AuthController } from './controllers/auth.controller';
import { authenticate } from '../common/guards/auth.guard';

const router = Router();
const authController = new AuthController();

router.post('/login', authController.login.bind(authController));
router.post('/logout', authenticate, authController.logout.bind(authController));
router.post('/refreshTokens', authController.refreshTokens.bind(authController));

export default router;

