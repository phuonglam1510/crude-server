import React, { useEffect } from 'react';
import { BrowserRouter, Routes, Route, Navigate, useParams } from 'react-router-dom';
import { ThemeProvider, createTheme, CssBaseline } from '@mui/material';
import { useTranslation } from 'react-i18next';
import { supportedLanguages, defaultLanguage } from './i18n/config';
import AdminLayout from './components/layout/AdminLayout';
import Dashboard from './pages/Dashboard/Dashboard';
import ResourcesList from './pages/Resources/ResourcesList';
import CreateResource from './pages/Resources/CreateResource';

const theme = createTheme();

const AppRoutes: React.FC = () => {
    const { lang } = useParams<{ lang: string }>();
    const { i18n } = useTranslation();

    useEffect(() => {
        if (lang && supportedLanguages.includes(lang)) {
            i18n.changeLanguage(lang);
        } else if (!lang) {
            i18n.changeLanguage(defaultLanguage);
        }
    }, [lang, i18n]);

    if (lang && !supportedLanguages.includes(lang)) {
        return <Navigate to={`/${defaultLanguage}/`} replace />;
    }

    return (
        <AdminLayout>
            <Routes>
                <Route path="/" element={<Dashboard />} />
                <Route path="/resources" element={<ResourcesList />} />
                <Route path="/resources/new" element={<CreateResource />} />
                <Route path="*" element={<Navigate to={`/${lang || defaultLanguage}/`} replace />} />
            </Routes>
        </AdminLayout>
    );
};

function App() {
    return (
        <ThemeProvider theme={theme}>
            <CssBaseline />
            <BrowserRouter>
                <Routes>
                    <Route path="/" element={<Navigate to={`/${defaultLanguage}/`} replace />} />
                    <Route path="/:lang/*" element={<AppRoutes />} />
                    <Route path="*" element={<Navigate to={`/${defaultLanguage}/`} replace />} />
                </Routes>
            </BrowserRouter>
        </ThemeProvider>
    );
}

export default App;
