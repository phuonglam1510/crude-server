import { create } from 'zustand';
import { persist, createJSONStorage } from 'zustand/middleware';
import { AxiosError } from 'axios';
import apiClient from '../config/api';
import phpApiClient from '../config/phpApi';

export interface PhpProperty {
    id: number;
    title?: string;
    house_address?: string;
    house_number?: string;
    area?: number;
    into_money?: number;
    province?: string;
    district?: string;
    status?: number;
    public?: number;
    public_approval?: number;
    created_at?: string;
    updated_at?: string;
    [key: string]: unknown;
}

interface PhpPropertiesState {
    token: string | null;
    properties: PhpProperty[];
    total: number;
    loading: boolean;
    error: string | null;
    setToken: (token: string | null) => void;
    login: (email: string, password: string) => Promise<{ success: boolean; error?: string }>;
    fetchProperties: (page?: number, size?: number) => Promise<void>;
    syncSelected: (houseIds: number[]) => Promise<{ jobId: string | null; error?: string }>;
    clearError: () => void;
    logout: () => void;
}

interface PhpApiResponse<T> {
    data: T;
    message?: string;
}

type PhpApiAuthResponse = PhpApiResponse<{
    token?: string;
    id?: number;
    email?: string;
    name?: string;
}>;

const getAuthHeaders = (token: string | null) => {
    if (!token) return {};
    return { Authorization: `Bearer ${token}` };
};

export const usePhpPropertiesStore = create<PhpPropertiesState>()(
    persist(
        (set, get) => ({
            token: null,
            properties: [],
            total: 0,
            loading: false,
            error: null,

            setToken: (token) => set({ token }),

            login: async (email, password) => {
                set({ loading: true, error: null });
                try {
                    const { data: responseData } = await phpApiClient.post<PhpApiAuthResponse>(
                        '/api/auth/signin/email',
                        { email, password }
                    );
                    const token = responseData.data?.token;
                    if (!token) {
                        set({ loading: false, error: 'No token in response' });
                        return { success: false, error: 'No token in response' };
                    }
                    set({ token, loading: false, error: null });
                    return { success: true };
                } catch (err) {
                    const axiosError = err as AxiosError<{ message?: string }>;
                    const errorMessage = axiosError.response?.data?.message ?? axiosError.message ?? 'Login failed';
                    set({ error: errorMessage, loading: false });
                    return { success: false, error: errorMessage };
                }
            },

            fetchProperties: async (page = 1, size = 20) => {
                const { token } = get();
                if (!token) {
                    set({ error: 'Please log in first' });
                    return;
                }
                set({ loading: true, error: null });
                try {
                    const { data } = await apiClient.get<{ data: PhpProperty[]; total: number }>(
                        '/api/php-properties',
                        {
                            params: { page, size },
                            headers: getAuthHeaders(token),
                        }
                    );
                    set({
                        properties: data?.data ?? [],
                        total: data?.total ?? 0,
                        loading: false,
                        error: null,
                    });
                } catch (err) {
                    const axiosError = err as AxiosError<{ message?: string }>;
                    const errorMessage = axiosError.response?.data?.message ?? axiosError.message ?? 'Failed to fetch properties';
                    set({ error: errorMessage, loading: false });
                }
            },

            syncSelected: async (houseIds) => {
                const { token } = get();
                if (!token) return { jobId: null, error: 'Please log in first' };
                set({ loading: true, error: null });
                try {
                    const { data } = await apiClient.post<{ job_id: string }>(
                        '/api/php-properties/sync',
                        { house_ids: houseIds },
                        { headers: getAuthHeaders(token) }
                    );
                    set({ loading: false });
                    return { jobId: data?.job_id ?? null };
                } catch (err) {
                    const axiosError = err as AxiosError<{ message?: string }>;
                    const errorMessage = axiosError.response?.data?.message ?? axiosError.message ?? 'Sync failed';
                    set({ error: errorMessage, loading: false });
                    return { jobId: null, error: errorMessage };
                }
            },

            clearError: () => set({ error: null }),
            logout: () => set({ token: null, properties: [], total: 0, error: null }),
        }),
        {
            name: 'php-properties-storage',
            storage: createJSONStorage(() => sessionStorage),
            partialize: (state) => ({ token: state.token }),
        }
    )
);
