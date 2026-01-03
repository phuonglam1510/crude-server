import React from 'react';
import {
    TextField,
    Button,
    Box,
    Paper,
    Typography,
    Stack,
} from '@mui/material';
import { useTranslation } from 'react-i18next';
import { CreateResourceInput } from '../../stores/resourceStore';

interface ResourceFormProps {
    initialValues?: CreateResourceInput;
    onSubmit: (data: CreateResourceInput) => Promise<void>;
    onCancel?: () => void;
    submitLabel?: string;
    title?: string;
    loading?: boolean;
}

const ResourceForm: React.FC<ResourceFormProps> = ({
    initialValues = { name: '', description: '' },
    onSubmit,
    onCancel,
    submitLabel,
    title,
    loading = false,
}) => {
    const { t } = useTranslation();
    const [formData, setFormData] = React.useState<CreateResourceInput>(initialValues);
    const [errors, setErrors] = React.useState<Partial<Record<keyof CreateResourceInput, string>>>({});

    const handleChange = (field: keyof CreateResourceInput) => (
        event: React.ChangeEvent<HTMLInputElement>
    ) => {
        setFormData((prev) => ({ ...prev, [field]: event.target.value }));
        if (errors[field]) {
            setErrors((prev) => ({ ...prev, [field]: undefined }));
        }
    };

    const validate = (): boolean => {
        const newErrors: Partial<Record<keyof CreateResourceInput, string>> = {};
        if (!formData.name.trim()) {
            newErrors.name = t('resources.nameRequired');
        }
        if (!formData.description.trim()) {
            newErrors.description = t('resources.descriptionRequired');
        }
        setErrors(newErrors);
        return Object.keys(newErrors).length === 0;
    };

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        if (validate()) {
            await onSubmit(formData);
        }
    };

    return (
        <Paper sx={{ p: 3 }}>
            <Typography variant="h5" gutterBottom>
                {title || t('resources.resourceForm')}
            </Typography>
            <Box component="form" onSubmit={handleSubmit} sx={{ mt: 2 }}>
                <Stack spacing={3}>
                    <TextField
                        fullWidth
                        label={t('common.name')}
                        value={formData.name}
                        onChange={handleChange('name')}
                        error={!!errors.name}
                        helperText={errors.name}
                        disabled={loading}
                        required
                    />
                    <TextField
                        fullWidth
                        label={t('common.description')}
                        value={formData.description}
                        onChange={handleChange('description')}
                        error={!!errors.description}
                        helperText={errors.description}
                        multiline
                        rows={4}
                        disabled={loading}
                        required
                    />
                    <Stack direction="row" spacing={2} justifyContent="flex-end">
                        {onCancel && (
                            <Button
                                variant="outlined"
                                onClick={onCancel}
                                disabled={loading}
                            >
                                {t('common.cancel')}
                            </Button>
                        )}
                        <Button
                            type="submit"
                            variant="contained"
                            disabled={loading}
                        >
                            {submitLabel || t('common.submit')}
                        </Button>
                    </Stack>
                </Stack>
            </Box>
        </Paper>
    );
};

export default ResourceForm;

