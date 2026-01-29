/**
 * Property entity interface matching PHP House model structure
 * This represents the data structure sent from Laravel PHP server
 */
export interface Property {
    // Basic identification
    id?: number;
    sign?: string;
    slug?: string;

    // Location information
    house_number?: string;
    house_address?: string;
    village?: string;
    district?: string;
    province?: string;
    city_id?: number;

    // Property details
    title?: string;
    property_type?: number; // Dictionary HouseType ID -> maps to loai-bds
    house_type?: number; // Maps to loai-nha
    floors?: number; // Maps to so-tang
    floor_lot?: string;
    block_section?: string;

    // Dimensions
    width?: number;
    length?: number;
    end_open?: number;
    area?: number; // dien-tich -> maps to dien-tich-bds taxonomy
    floor_area?: number;

    // Financial
    into_money?: number; // Price -> maps to muc-gia-bds
    brokerage_rate?: number;
    brokerage_fee?: number;
    commission?: number;

    // Property features
    number_bedroom?: number;
    number_wc?: number;
    dinning_room?: number;
    kitchen?: number;
    terrace?: number;
    car_parking?: number;

    // Directions (arrays of IDs from relationships)
    house_directions?: number[]; // HouseDirection.direction IDs -> maps to huong-nha
    house_balcony_directions?: number[]; // HouseBalconyDirection.balcony IDs -> maps to huong-ban-cong

    // Legal & ownership
    ownership?: number; // Dictionary Ownership ID -> maps to giay-to-phap-ly
    purpose?: string;

    // Images (JSON arrays or strings)
    internal_image?: string | string[];
    public_image?: string | string[];
    file_ids?: string | number[];

    // Content/Descriptions
    description?: string;
    descriptions?: string;
    internalDescription?: string;
    initialDescription?: string;
    key_word?: string;

    // Status & visibility
    status?: string;
    approve?: number;
    public?: number;
    web?: number;
    public_approval?: number;

    // Additional info
    street_type?: number;
    wide_street?: number;
    type_news?: string;
    type_news_value?: string;
    type_news_day?: number;

    // Relationships (may be included in payload)
    city?: { id: number; name?: string }; // Dictionary City -> maps to tinh-thanh
    district_obj?: { id: number; name?: string }; // Dictionary District -> maps to quan-huyen
    province_obj?: { id: number; name?: string }; // Dictionary Province
    property_type_obj?: { id: number; name?: string }; // Dictionary HouseType -> maps to loai-bds
    house_type_obj?: { id: number; name?: string }; // Maps to loai-nha

    // Tags (array of tag IDs from HouseTag relationship)
    tags?: number[]; // Tag IDs -> maps to tien-ich or bat-dong-san

    // Interior (maps to noi-that-bds)
    // This might come from a specific field or be calculated

    // Area range (calculated from area field)
    // Maps to dien-tich-bds taxonomy

    // Price range (calculated from into_money field)
    // Maps to muc-gia-bds taxonomy

    // Timestamps
    created_at?: string | Date;
    updated_at?: string | Date;

    // Other fields (less commonly used)
    user_id?: number;
    customer_id?: number;
    project_id?: number;
    suitable_customer?: string;
    offered_customer?: string;
    seen_customer?: string;
    require_info_customer?: string;
    deposit_customer?: string;
    total_view?: number;
    recommnend_quantity?: number;
    seen_quantity?: number;
    postQuantity?: number;
    reject_public_condition?: string;
    reject_web_condition?: string;
    reason_stop_selling?: string;
}
