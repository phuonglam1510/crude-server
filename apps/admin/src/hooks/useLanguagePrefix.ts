import { useParams } from 'react-router-dom';

/**
 * Custom hook to get the language prefix from the URL
 * Returns the language prefix (e.g., "/en" or "/vi") or empty string if no language in URL
 */
export const useLanguagePrefix = (): string => {
    const { lang } = useParams<{ lang: string }>();
    return lang ? `/${lang}` : '';
};

