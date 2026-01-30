import axios from 'axios';

const PHP_API_URL = import.meta.env.VITE_PHP_API_URL || 'https://crm.thinhgialand.com';

const phpApiClient = axios.create({
    baseURL: PHP_API_URL,
    headers: {
        'Content-Type': 'application/json',
    },
    timeout: 15000,
});

export default phpApiClient;
