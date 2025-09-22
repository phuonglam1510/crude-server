import { And, ILike, Like, Repository } from 'typeorm';
import { Resource } from '../entities/resource.entity';
import { AppDataSource } from '../../../configs/ormconfig';

export class ResourceService {
    private resourceRepository: Repository<Resource>;

    constructor() {
        this.resourceRepository = AppDataSource.getRepository(Resource);
    }

    async createResource(name: string, description: string): Promise<Resource> {
        const resource = this.resourceRepository.create({ name, description });
        return this.resourceRepository.save(resource);
    }

    async getResources(filter: string = ''): Promise<Resource[]> {
        return this.resourceRepository.find({
            where: [{
                name: ILike(`%${filter}%`)
            }, {
                description: ILike(`%${filter}%`)
            }]
        })
    }

    async getResourceById(id: string): Promise<Resource | null> {
        return this.resourceRepository.findOne({ where: { id } });
    }

    async updateResource(id: string, name: string, description: string): Promise<Resource | undefined> {
        const resource = await this.getResourceById(id);
        if (!resource) {
            return undefined;
        }
        resource.name = name;
        resource.description = description;
        return this.resourceRepository.save(resource);
    }

    async deleteResource(id: string): Promise<boolean> {
        const result = await this.resourceRepository.delete(id);
        return result.affected !== 0;
    }
}