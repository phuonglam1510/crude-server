import React, { useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import {
    Box,
    Typography,
    Button,
    IconButton,
    Alert,
    Stack,
} from '@mui/material';
import { DataGrid, GridColDef } from '@mui/x-data-grid';
import AddIcon from '@mui/icons-material/Add';
import EditIcon from '@mui/icons-material/Edit';
import DeleteIcon from '@mui/icons-material/Delete';
import { useTranslation } from 'react-i18next';
import useResourceStore from '../../stores/resourceStore';
import { useLanguagePrefix } from '../../hooks/useLanguagePrefix';

const ResourcesList: React.FC = () => {
    const { t } = useTranslation();
    const navigate = useNavigate();
    const languagePrefix = useLanguagePrefix();
    const { resources, loading, error, fetchResources, deleteResource } = useResourceStore();

    useEffect(() => {
        fetchResources();
    }, [fetchResources]);

    const handleDelete = async (id: string) => {
        if (window.confirm(t('resources.deleteConfirm'))) {
            const success = await deleteResource(id);
            if (success) {
                // Resource deleted, list will update automatically
            }
        }
    };

    const columns: GridColDef[] = [
        { 
            field: 'name', 
            headerName: t('common.name'), 
            width: 200,
            flex: 1,
        },
        {
            field: 'description',
            headerName: t('common.description'),
            width: 400,
            flex: 2,
            renderCell: (params) => (
                <Typography 
                    variant="body2" 
                    sx={{ 
                        maxWidth: 400, 
                        overflow: 'hidden', 
                        textOverflow: 'ellipsis',
                        whiteSpace: 'nowrap'
                    }}
                >
                    {params.value}
                </Typography>
            ),
        },
        {
            field: 'createdAt',
            headerName: t('common.createdAt'),
            width: 180,
            renderCell: (params) => params.value ? new Date(params.value).toLocaleDateString() : '-',
        },
        {
            field: 'actions',
            headerName: t('common.actions'),
            width: 150,
            sortable: false,
            filterable: false,
            renderCell: (params) => (
                <Stack direction="row" spacing={1}>
                    <IconButton
                        size="small"
                        onClick={() => navigate(`${languagePrefix}/resources/${params.row.id}/edit`)}
                        color="primary"
                    >
                        <EditIcon />
                    </IconButton>
                    <IconButton
                        size="small"
                        onClick={() => handleDelete(params.row.id)}
                        color="error"
                    >
                        <DeleteIcon />
                    </IconButton>
                </Stack>
            ),
        },
    ];

    return (
        <Box>
            <Stack direction="row" justifyContent="space-between" alignItems="center" mb={3}>
                <Typography variant="h4">{t('resources.title')}</Typography>
                <Button
                    variant="contained"
                    startIcon={<AddIcon />}
                    onClick={() => navigate(`${languagePrefix}/resources/new`)}
                >
                    {t('resources.createResource')}
                </Button>
            </Stack>

            {error && (
                <Alert severity="error" sx={{ mb: 2 }}>
                    {error}
                </Alert>
            )}

            <Box sx={{ height: 400, width: '100%' }}>
                <DataGrid
                    rows={resources}
                    columns={columns}
                    loading={loading}
                    disableRowSelectionOnClick
                    initialState={{
                        pagination: {
                            paginationModel: { page: 0, pageSize: 10 },
                        },
                    }}
                    pageSizeOptions={[5, 10, 25]}
                    sx={{
                        '& .MuiDataGrid-cell:focus': {
                            outline: 'none',
                        },
                    }}
                />
            </Box>
        </Box>
    );
};

export default ResourcesList;

