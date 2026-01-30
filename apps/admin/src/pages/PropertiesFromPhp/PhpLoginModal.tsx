import React, { useState } from 'react';
import {
    Dialog,
    DialogTitle,
    DialogContent,
    DialogActions,
    Button,
    TextField,
    Stack,
    CircularProgress,
} from '@mui/material';
import LoginIcon from '@mui/icons-material/Login';
import { useTranslation } from 'react-i18next';

export interface PhpLoginModalProps {
    open: boolean;
    onClose: () => void;
    onSuccess: () => void;
    login: (email: string, password: string) => Promise<{ success: boolean; error?: string }>;
    allowClose?: boolean;
}

const PhpLoginModal: React.FC<PhpLoginModalProps> = ({
    open,
    onClose,
    onSuccess,
    login,
    allowClose = true,
}) => {
    const { t } = useTranslation();
    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');
    const [loading, setLoading] = useState(false);

    const handleSubmit = async () => {
        if (!email.trim() || !password) return;
        setLoading(true);
        const result = await login(email.trim(), password);
        setLoading(false);
        if (result.success) {
            setEmail('');
            setPassword('');
            onSuccess();
            onClose();
        }
    };

    const handleBackdropClose = () => {
        if (allowClose) {
            setEmail('');
            setPassword('');
            onClose();
        }
    };

    const handleCancel = () => {
        setEmail('');
        setPassword('');
        onClose();
    };

    return (
        <Dialog open={open} onClose={allowClose ? handleBackdropClose : undefined} maxWidth="xs" fullWidth>
            <DialogTitle>{t('properties.loginToPhp')}</DialogTitle>
            <DialogContent>
                <Stack spacing={2} sx={{ pt: 1 }}>
                    <TextField
                        label={t('properties.email')}
                        type="email"
                        value={email}
                        onChange={(e) => setEmail(e.target.value)}
                        fullWidth
                        autoComplete="email"
                    />
                    <TextField
                        label={t('properties.password')}
                        type="password"
                        value={password}
                        onChange={(e) => setPassword(e.target.value)}
                        fullWidth
                        autoComplete="current-password"
                    />
                </Stack>
            </DialogContent>
            <DialogActions>
                <Button onClick={handleCancel}>
                    {t('common.cancel')}
                </Button>
                <Button
                    variant="contained"
                    onClick={handleSubmit}
                    disabled={loading || !email.trim() || !password}
                    startIcon={loading ? <CircularProgress size={20} /> : <LoginIcon />}
                >
                    {loading ? t('common.loading') : t('properties.login')}
                </Button>
            </DialogActions>
        </Dialog>
    );
};

export default PhpLoginModal;
