import axios, { type AxiosInstance } from 'axios';

let axiosInstance: AxiosInstance | null = null;

export function initializeAxios() {
    const baseUrl = `/gatekeeper/api`.replace(/\/+/g, '/');

    axiosInstance = axios.create({
        baseURL: baseUrl,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
        },
    });

    const token = document.head.querySelector("meta[name='csrf-token']") as HTMLMetaElement;
    if (token) {
        axiosInstance.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
    }
}

export function useAxios(): AxiosInstance {
    if (!axiosInstance) {
        throw new Error('Axios has not been initialized. Call initAxios() first.');
    }
    return axiosInstance;
}
