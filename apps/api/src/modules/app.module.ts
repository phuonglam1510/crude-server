import { Express } from 'express';
import resourceRoutes from './resource/resource.routes';
import resourceAdminRoutes from './resource/resource-admin.routes';
import authRoutes from './auth/auth.routes';
import syncRoutes from './sync/sync.routes';
import phpPropertiesRoutes from './php-properties/php-properties.routes';

export function registerRoutes(app: Express): void {
    // Public routes
    app.use('/api/resources', resourceRoutes);

    // Auth routes
    app.use('/api/auth', authRoutes);

    // Admin routes (protected)
    app.use('/api/admin/resources', resourceAdminRoutes);

    // Sync routes (for Laravel PHP server to call)
    app.use('/api/sync', syncRoutes);

    // PHP properties: list from PHP, auth, sync selected to WP
    app.use('/api/php-properties', phpPropertiesRoutes);
}