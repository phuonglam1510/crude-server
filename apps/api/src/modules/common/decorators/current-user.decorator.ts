import { AuthenticatedRequest } from '../guards/auth.guard';

export function getCurrentUser(req: AuthenticatedRequest): { userId: string; email: string } | undefined {
    return req.user;
}

