import { create } from 'zustand';
import { AxiosError } from 'axios';
import apiClient from '../config/api';

export interface Resource {
    id: string;
    name: string;
    description: string;
    createdAt?: Date;
    updatedAt?: Date;
}

export interface CreateResourceInput {
    name: string;
    description: string;
}

interface ResourceStoreState {
    resources: Resource[];
    loading: boolean;
    error: string | null;
    fetchResources: (filter?: string) => Promise<void>;
    createResource: (data: CreateResourceInput) => Promise<Resource | null>;
    updateResource: (id: string, data: CreateResourceInput) => Promise<Resource | null>;
    deleteResource: (id: string) => Promise<boolean>;
    getResourceById: (id: string) => Promise<Resource | null>;
}

const useResourceStore = create<ResourceStoreState>((set, get) => ({
    resources: [],
    loading: false,
    error: null,
    fetchResources: async (filter?: string) => {
        set({ loading: true, error: null });
        try {
            const params = filter ? { params: { filter } } : {};
            const response = await apiClient.get<Resource[]>('/api/resources', params);
            set({ resources: response.data, loading: false });
        } catch (err) {
            const axiosError = err as AxiosError<{ message?: string }>;
            const errorMessage = axiosError.response?.data?.message || axiosError.message || 'Failed to fetch resources';
            set({ error: errorMessage, loading: false });
        }
    },
    createResource: async (data: CreateResourceInput) => {
        set({ loading: true, error: null });
        try {
            const response = await apiClient.post<Resource>('/api/resources', data);
            const newResource = response.data;
            set((state) => ({
                resources: [...state.resources, newResource],
                loading: false,
            }));
            return newResource;
        } catch (err) {
            const axiosError = err as AxiosError<{ message?: string }>;
            const errorMessage = axiosError.response?.data?.message || axiosError.message || 'Failed to create resource';
            set({ error: errorMessage, loading: false });
            return null;
        }
    },
    updateResource: async (id: string, data: CreateResourceInput) => {
        set({ loading: true, error: null });
        try {
            const response = await apiClient.put<Resource>(`/api/resources/${id}`, data);
            const updatedResource = response.data;
            set((state) => ({
                resources: state.resources.map((r) => (r.id === id ? updatedResource : r)),
                loading: false,
            }));
            return updatedResource;
        } catch (err) {
            const axiosError = err as AxiosError<{ message?: string }>;
            const errorMessage = axiosError.response?.data?.message || axiosError.message || 'Failed to update resource';
            set({ error: errorMessage, loading: false });
            return null;
        }
    },
    deleteResource: async (id: string) => {
        set({ loading: true, error: null });
        try {
            await apiClient.delete(`/api/resources/${id}`);
            set((state) => ({
                resources: state.resources.filter((r) => r.id !== id),
                loading: false,
            }));
            return true;
        } catch (err) {
            const axiosError = err as AxiosError<{ message?: string }>;
            const errorMessage = axiosError.response?.data?.message || axiosError.message || 'Failed to delete resource';
            set({ error: errorMessage, loading: false });
            return false;
        }
    },
    getResourceById: async (id: string) => {
        set({ loading: true, error: null });
        try {
            const response = await apiClient.get<Resource>(`/api/resources/${id}`);
            set({ loading: false });
            return response.data;
        } catch (err) {
            const axiosError = err as AxiosError<{ message?: string }>;
            const errorMessage = axiosError.response?.data?.message || axiosError.message || 'Failed to fetch resource';
            set({ error: errorMessage, loading: false });
            return null;
        }
    },
}));

export default useResourceStore;

