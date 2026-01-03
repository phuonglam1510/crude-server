import 'reflect-metadata';
import dotenv from 'dotenv';

// Load environment variables from .env file
dotenv.config();

import express from 'express';
import cors from 'cors';
import { AppDataSource } from './configs/ormconfig';
import { registerRoutes } from './modules/app.module';

const app = express();
const PORT = process.env.PORT || 4000;

// Enable CORS for frontend apps
app.use(cors({
    origin: ['http://localhost:3000', 'http://localhost:3001', 'http://localhost:5173'],
    credentials: true
}));

app.use(express.json());

AppDataSource.initialize()
    .then(() => {
        console.log('Data Source has been initialized successfully.');
        registerRoutes(app);

        app.listen(PORT, () => {
            console.log(`Server is running on http://localhost:${PORT}`);
        });
    })
    .catch(error => {
        console.error('Error during Data Source initialization:', error);
    });