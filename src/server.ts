import 'reflect-metadata';
import express from 'express';
import { AppDataSource } from './configs/ormconfig';
import { registerRoutes } from './modules/app.module';

const app = express();
const PORT = process.env.PORT || 3000;

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