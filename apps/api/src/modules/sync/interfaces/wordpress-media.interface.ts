/**
 * WordPress REST API Media/Attachment interface
 * Used when creating or updating media via POST /wp/v2/media or PATCH /wp/v2/media/:id
 */
export interface WordPressMediaCreate {
    date?: string | null;
    date_gmt?: string | null;
    slug?: string;
    status?: 'publish' | 'future' | 'draft' | 'pending' | 'private' | 'acf-disabled';
    title?: { raw?: string };
    author?: number;
    featured_media?: number;
    comment_status?: 'open' | 'closed';
    ping_status?: 'open' | 'closed';
    meta?: { _acf_changed?: boolean; [key: string]: any };
    template?: string;
    alt_text?: string;
    caption?: { raw?: string };
    description?: { raw?: string };
    /** ID of the post the attachment is attached to */
    post?: number;
}
