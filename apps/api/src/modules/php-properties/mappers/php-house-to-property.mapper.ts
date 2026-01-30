import { Property } from '../../sync/entities/property.entity';

/**
 * Map PHP House API response to our Property entity (for sync).
 * PHP returns snake_case and relations (house_direction, house_balcony_direction, etc.).
 */
export function mapPhpHouseToProperty(phpHouse: any): Property {
    if (!phpHouse || typeof phpHouse !== 'object') return phpHouse as Property;

    const house_directions = Array.isArray(phpHouse.house_direction)
        ? phpHouse.house_direction.map((d: any) => ({ direction: d.direction, id: d.id }))
        : [];
    const house_balcony_directions = Array.isArray(phpHouse.house_balcony_direction)
        ? phpHouse.house_balcony_direction.map((d: any) => ({ direction: d.direction, id: d.id }))
        : undefined;
    const tags = Array.isArray(phpHouse.house_tag)
        ? phpHouse.house_tag.map((t: any) => (typeof t === 'object' && t?.tag_id != null ? t.tag_id : t)).filter((id: any) => id != null)
        : undefined;

    return {
        id: phpHouse.id,
        sign: phpHouse.sign,
        slug: phpHouse.slug,
        house_number: phpHouse.house_number,
        house_address: phpHouse.house_address,
        village: phpHouse.village,
        district: phpHouse.district,
        province: phpHouse.province,
        city_id: phpHouse.city_id,
        title: phpHouse.title,
        property_type: phpHouse.property_type,
        house_type: phpHouse.house_type,
        floors: phpHouse.floors,
        floor_lot: phpHouse.floor_lot,
        block_section: phpHouse.block_section,
        width: phpHouse.width,
        length: phpHouse.length,
        end_open: phpHouse.end_open,
        area: phpHouse.area,
        floor_area: phpHouse.floor_area,
        into_money: phpHouse.into_money,
        brokerage_rate: phpHouse.brokerage_rate,
        brokerage_fee: phpHouse.brokerage_fee,
        commission: phpHouse.commission,
        number_bedroom: phpHouse.number_bedroom,
        number_wc: phpHouse.number_wc,
        dinning_room: phpHouse.dinning_room,
        kitchen: phpHouse.kitchen,
        terrace: phpHouse.terrace,
        car_parking: phpHouse.car_parking,
        house_directions: house_directions?.length ? house_directions : undefined,
        house_balcony_directions: house_balcony_directions?.length ? house_balcony_directions : undefined,
        ownership: phpHouse.ownership,
        purpose: phpHouse.purpose,
        internal_image: phpHouse.internal_image,
        public_image: phpHouse.public_image,
        file_ids: phpHouse.file_ids,
        description: phpHouse.description,
        descriptions: phpHouse.descriptions,
        internalDescription: phpHouse.internalDescription,
        initialDescription: phpHouse.initialDescription,
        key_word: phpHouse.key_word,
        status: phpHouse.status != null ? String(phpHouse.status) : undefined,
        approve: phpHouse.approve,
        public: phpHouse.public,
        web: phpHouse.web,
        public_approval: phpHouse.public_approval,
        street_type: phpHouse.street_type,
        wide_street: phpHouse.wide_street,
        type_news: phpHouse.type_news,
        type_news_value: phpHouse.type_news_value,
        type_news_day: phpHouse.type_news_day,
        city: phpHouse.city,
        district_obj: phpHouse.district_obj,
        province_obj: phpHouse.province_obj,
        property_type_obj: phpHouse.property_type_obj,
        house_type_obj: phpHouse.house_type_obj,
        tags,
        created_at: phpHouse.created_at,
        updated_at: phpHouse.updated_at,
        user_id: phpHouse.user_id,
        customer_id: phpHouse.customer_id,
        project_id: phpHouse.project_id,
        suitable_customer: phpHouse.suitable_customer,
        offered_customer: phpHouse.offered_customer,
        seen_customer: phpHouse.seen_customer,
        require_info_customer: phpHouse.require_info_customer,
        deposit_customer: phpHouse.deposit_customer,
        total_view: phpHouse.total_view,
        recommnend_quantity: phpHouse.recommnend_quantity,
        seen_quantity: phpHouse.seen_quantity,
        postQuantity: phpHouse.postQuantity,
        reject_public_condition: phpHouse.reject_public_condition,
        reject_web_condition: phpHouse.reject_web_condition,
        reason_stop_selling: phpHouse.reason_stop_selling,
    } as Property;
}
