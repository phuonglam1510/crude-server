import { Request, Response } from 'express';
import { ResourceService } from '../services/resource.service';

const resourceService = new ResourceService();

export class ResourceController {
    async create(req: Request, res: Response): Promise<void> {
        try {
            const { name, description } = req.body;
            const newResource = await resourceService.createResource(name, description);
            res.status(201).json(newResource);
        } catch (error) {
            res.status(500).json({ message: 'Error creating resource', error });
        }
    }

    async list(req: Request, res: Response): Promise<void> {
        try {
            const { filter } = req.query;
            const resources = await resourceService.getResources(filter as string);
            res.status(200).json(resources);
        } catch (error) {
            res.status(500).json({ message: 'Error listing resources', error });
        }
    }

    async getById(req: Request, res: Response): Promise<void> {
        try {
            const { id } = req.params;
            const resource = await resourceService.getResourceById(id);
            if (resource) {
                res.status(200).json(resource);
            } else {
                res.status(404).json({ message: 'Resource not found' });
            }
        } catch (error) {
            res.status(500).json({ message: 'Error getting resource', error });
        }
    }

    async update(req: Request, res: Response): Promise<void> {
        try {
            const { id } = req.params;
            const { name, description } = req.body;
            const updatedResource = await resourceService.updateResource(id, name, description);
            if (updatedResource) {
                res.status(200).json(updatedResource);
            } else {
                res.status(404).json({ message: 'Resource not found' });
            }
        } catch (error) {
            res.status(500).json({ message: 'Error updating resource', error });
        }
    }

    async delete(req: Request, res: Response): Promise<void> {
        try {
            const { id } = req.params;
            const success = await resourceService.deleteResource(id);
            if (success) {
                res.status(204).send(); // No content
            } else {
                res.status(404).json({ message: 'Resource not found' });
            }
        } catch (error) {
            res.status(500).json({ message: 'Error deleting resource', error });
        }
    }
}