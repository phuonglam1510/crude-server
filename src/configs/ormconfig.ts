import { DataSource, DataSourceOptions } from 'typeorm';
import { Resource } from '../modules/resource/entities/resource.entity';

export const AppDataSource = new DataSource({
    type: 'postgres',
    host: 'localhost',
    port: 5432,
    username: 'admin',
    password: 'admin',
    database: 'mydatabase',
    synchronize: true,
    logging: true,
    entities: [Resource]
});