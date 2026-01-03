import { create } from 'zustand';
import { AxiosError } from 'axios';
import apiClient from '../config/api';

interface Item {
    id: string;
    name: string;
    description?: string;
    createdAt?: Date;
    updatedAt?: Date;
}

interface ItemStoreState {
    items: Item[];
    loading: boolean;
    error: string | null;
    fetchItems: () => Promise<void>;
}

const useItemStore = create<ItemStoreState>((set) => ({
    items: [],
    loading: false,
    error: null,
    fetchItems: async () => {
        set({ loading: true, error: null });
        try {
            const response = await apiClient.get<Item[]>('/api/resources');
            set({ items: response.data, loading: false });
        } catch (err) {
            const axiosError = err as AxiosError<{ message?: string }>;
            const errorMessage = axiosError.response?.data?.message || axiosError.message || 'Failed to fetch from API';
            set({ error: errorMessage, loading: false });
        }
    },
}));

export default useItemStore;
