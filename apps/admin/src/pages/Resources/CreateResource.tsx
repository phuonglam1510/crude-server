import React from 'react';
import { useNavigate } from 'react-router-dom';
import { Box, Typography, Alert } from '@mui/material';
import { useTranslation } from 'react-i18next';
import useResourceStore from '../../stores/resourceStore';
import ResourceForm from '../../components/common/ResourceForm';
import { CreateResourceInput } from '../../stores/resourceStore';
import { useLanguagePrefix } from '../../hooks/useLanguagePrefix';

const CreateResource: React.FC = () => {
    const { t } = useTranslation();
    const navigate = useNavigate();
    const languagePrefix = useLanguagePrefix();
    const { createResource, loading, error } = useResourceStore();

    const handleSubmit = async (data: CreateResourceInput) => {
        const resource = await createResource(data);
        if (resource) {
            navigate(`${languagePrefix}/resources`);
        }
    };

    const handleCancel = () => {
        navigate(`${languagePrefix}/resources`);
    };

    return (
        <Box>
            <Typography variant="h4" gutterBottom>
                {t('resources.createResource')}
            </Typography>

            {error && (
                <Alert severity="error" sx={{ mb: 2 }}>
                    {error}
                </Alert>
            )}

            <ResourceForm
                onSubmit={handleSubmit}
                onCancel={handleCancel}
                submitLabel={t('common.create')}
                title={t('resources.createNewResource')}
                loading={loading}
            />
        </Box>
    );
};

export default CreateResource;

