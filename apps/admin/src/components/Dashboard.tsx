import React, { useEffect } from 'react';
import { Card, CardContent, Typography, CircularProgress } from '@mui/material';
import useItemStore from '../stores/itemStore';

const Dashboard = () => {
    const { items, loading, error, fetchItems } = useItemStore();

    useEffect(() => {
        fetchItems();
    }, [fetchItems]);

    return (
        <Card>
            <CardContent>
                <Typography variant="h5">API Data</Typography>
                {loading && <CircularProgress />}
                {error && <Typography color="error">{error}</Typography>}
                {!loading && !error && (
                    <ul>
                        {items.map((item) => (
                            <li key={item.id}>{item.name}</li>
                        ))}
                    </ul>
                )}
            </CardContent>
        </Card>
    );
};

export default Dashboard;
