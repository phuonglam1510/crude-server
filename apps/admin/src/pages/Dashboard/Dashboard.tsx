import React, { useEffect } from 'react';
import { Box, Typography, Paper, Grid } from '@mui/material';
import { useTranslation } from 'react-i18next';
import useResourceStore from '../../stores/resourceStore';

const Dashboard: React.FC = () => {
    const { t } = useTranslation();
    const { resources, fetchResources } = useResourceStore();

    useEffect(() => {
        fetchResources();
    }, [fetchResources]);

    return (
        <Box>
            <Typography variant="h4" gutterBottom>
                {t('dashboard.title')}
            </Typography>
            <Grid container spacing={3} sx={{ mt: 2 }}>
                <Grid  xs={12} md={4}>
                    <Paper sx={{ p: 3, textAlign: 'center' }}>
                        <Typography variant="h3" color="primary">
                            {resources.length}
                        </Typography>
                        <Typography variant="h6" color="text.secondary">
                            {t('dashboard.totalResources')}
                        </Typography>
                    </Paper>
                </Grid>
            </Grid>
        </Box>
    );
};

export default Dashboard;

