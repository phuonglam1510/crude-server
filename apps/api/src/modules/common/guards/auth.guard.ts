import { Request, Response, NextFunction } from 'express';
import { JwtUtil } from '../../auth/utils/jwt.util';
import { AuthService } from '../../auth/services/auth.service';

const authService = new AuthService();

export interface AuthenticatedRequest extends Request {
    user?: {
        userId: string;
        email: string;
    };
}

export async function authenticate(req: AuthenticatedRequest, res: Response, next: NextFunction): Promise<void> {
    try {
        const authHeader = req.headers.authorization;

        if (!authHeader || !authHeader.startsWith('Bearer ')) {
            res.status(401).json({ message: 'No token provided' });
            return;
        }

        const token = authHeader.substring(7); // Remove 'Bearer ' prefix

        // Verify token
        const payload = JwtUtil.verifyAccessToken(token);

        // Optionally verify user still exists
        const user = await authService.getUserById(payload.userId);
        if (!user) {
            res.status(401).json({ message: 'User not found' });
            return;
        }

        // Attach user info to request
        req.user = {
            userId: payload.userId,
            email: payload.email,
        };

        next();
    } catch (error) {
        const message = error instanceof Error ? error.message : 'Invalid token';
        res.status(401).json({ message });
    }
}

