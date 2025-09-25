import { Express } from 'express';
import resourceRoutes from './resource/resource.routes';

export function registerRoutes(app: Express): void {
    app.use('/api/resources', resourceRoutes);
}