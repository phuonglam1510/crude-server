import { Express } from 'express';
import resourceRoutes from './resource/resource.routes';
import resourceAdminRoutes from './resource/resource-admin.routes';
import authRoutes from './auth/auth.routes';

export function registerRoutes(app: Express): void {
    // Public routes
    app.use('/api/resources', resourceRoutes);
    
    // Auth routes
    app.use('/api/auth', authRoutes);
    
    // Admin routes (protected)
    app.use('/api/admin/resources', resourceAdminRoutes);
}