import { DataSource, DataSourceOptions } from 'typeorm';
import { Resource } from '../modules/resource/entities/resource.entity';
import { User } from '../modules/auth/entities/user.entity';

// Parse DATABASE_URL if provided, otherwise use individual env vars or defaults
const getDbConfig = (): DataSourceOptions => {
    const databaseUrl = process.env.DATABASE_URL;
    
    if (databaseUrl) {
        // Parse DATABASE_URL: postgresql://user:password@host:port/database
        const url = new URL(databaseUrl);
        return {
            type: 'postgres',
            host: url.hostname,
            port: parseInt(url.port) || 5432,
            username: url.username,
            password: url.password,
            database: url.pathname.slice(1), // Remove leading '/'
            synchronize: true,
            logging: true,
            entities: [Resource, User]
        };
    }
    
    // Fallback to individual environment variables or defaults
    return {
        type: 'postgres',
        host: process.env.DB_HOST || 'localhost',
        port: parseInt(process.env.DB_PORT || '5432'),
        username: process.env.DB_USERNAME || 'admin',
        password: process.env.DB_PASSWORD || 'admin',
        database: process.env.DB_NAME || 'mydatabase',
        synchronize: true,
        logging: true,
        entities: [Resource]
    };
};

export const AppDataSource = new DataSource(getDbConfig());