import { Request, Response } from 'express';
import { AuthService } from '../services/auth.service';

const authService = new AuthService();

export class AuthController {
    async login(req: Request, res: Response): Promise<void> {
        try {
            const { email, password } = req.body;

            if (!email || !password) {
                res.status(400).json({ message: 'Email and password are required' });
                return;
            }

            const result = await authService.login(email, password);

            res.status(200).json({
                user: result.user,
                accessToken: result.tokens.accessToken,
                refreshToken: result.tokens.refreshToken,
            });
        } catch (error) {
            const message = error instanceof Error ? error.message : 'Error during login';
            res.status(401).json({ message });
        }
    }

    async logout(req: Request, res: Response): Promise<void> {
        try {
            const userId = (req as any).user?.userId;
            if (!userId) {
                res.status(401).json({ message: 'Unauthorized' });
                return;
            }

            await authService.logout(userId);
            res.status(200).json({ message: 'Logged out successfully' });
        } catch (error) {
            res.status(500).json({ message: 'Error during logout', error });
        }
    }

    async refreshTokens(req: Request, res: Response): Promise<void> {
        try {
            const { refreshToken } = req.body;

            if (!refreshToken) {
                res.status(400).json({ message: 'Refresh token is required' });
                return;
            }

            const tokens = await authService.refreshTokens(refreshToken);

            res.status(200).json({
                accessToken: tokens.accessToken,
                refreshToken: tokens.refreshToken,
            });
        } catch (error) {
            const message = error instanceof Error ? error.message : 'Error refreshing tokens';
            res.status(401).json({ message });
        }
    }
}

