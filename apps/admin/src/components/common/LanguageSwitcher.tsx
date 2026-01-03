import React from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import { Select, MenuItem, FormControl, SelectChangeEvent } from '@mui/material';
import { useTranslation } from 'react-i18next';
import { supportedLanguages } from '../../i18n/config';

const LanguageSwitcher: React.FC = () => {
    const { t, i18n } = useTranslation();
    const navigate = useNavigate();
    const { lang } = useParams<{ lang: string }>();
    const currentLang = lang || i18n.language;

    const handleLanguageChange = (event: SelectChangeEvent<string>) => {
        const newLang = event.target.value;
        const currentPath = window.location.pathname;
        
        // Replace the language prefix in the URL
        const pathWithoutLang = currentPath.replace(/^\/(en|vi)/, '');
        const newPath = `/${newLang}${pathWithoutLang || '/'}`;
        
        i18n.changeLanguage(newLang);
        navigate(newPath);
    };

    return (
        <FormControl size="small" sx={{ minWidth: 100 }}>
            <Select
                value={currentLang}
                onChange={handleLanguageChange}
                sx={{
                    color: 'inherit',
                    '& .MuiOutlinedInput-notchedOutline': {
                        borderColor: 'rgba(255, 255, 255, 0.23)',
                    },
                    '&:hover .MuiOutlinedInput-notchedOutline': {
                        borderColor: 'rgba(255, 255, 255, 0.5)',
                    },
                    '& .MuiSvgIcon-root': {
                        color: 'inherit',
                    },
                }}
            >
                <MenuItem value="en">English</MenuItem>
                <MenuItem value="vi">Tiếng Việt</MenuItem>
            </Select>
        </FormControl>
    );
};

export default LanguageSwitcher;

