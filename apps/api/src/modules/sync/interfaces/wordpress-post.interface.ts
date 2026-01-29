/**
 * WordPress REST API Post/Collection interface
 * Based on the provided WordPress API specification
 */
export interface WordPressPost {
    date?: string | null;
    date_gmt?: string | null;
    slug?: string;
    status?: 'publish' | 'future' | 'draft' | 'pending' | 'private' | 'acf-disabled';
    password?: string;
    title?: {
        raw?: string;
        rendered?: string;
    };
    content?: {
        raw?: string;
        rendered?: string;
        block_version?: number;
        protected?: boolean;
    };
    featured_media?: number;
    meta?: {
        _acf_changed?: boolean;
        [key: string]: any;
    };
    template?: string;
    'dien-tich-bds'?: number[];
    'giay-to-phap-ly'?: number[];
    'huong-ban-cong'?: number[];
    'huong-nha'?: number[];
    'loai-bds'?: number[];
    'loai-nha'?: number[];
    'bat-dong-san'?: number[];
    'muc-gia-bds'?: number[];
    'noi-that-bds'?: number[];
    'phuong-xa'?: number[];
    'quan-huyen'?: number[];
    'so-tang'?: number[];
    'tien-ich'?: number[];
    'tinh-thanh'?: number[];
}
