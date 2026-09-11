import { defineStore } from 'pinia';
import api from '@/plugins/axios';

export const useAuthStore = defineStore('auth', {
    state: () => ({
        token: localStorage.getItem('auth_token'),
        tenantId: localStorage.getItem('tenant_id'),
        user: JSON.parse(localStorage.getItem('auth_user') ?? 'null'),
    }),
    actions: {
        setTenantId(tenantId) {
            this.tenantId = tenantId;
            localStorage.setItem('tenant_id', tenantId);
        },
        persistSession(payload) {
            this.token = payload.access_token;
            this.user = payload.user ?? null;

            if (payload.access_token) {
                localStorage.setItem('auth_token', payload.access_token);
            }

            localStorage.setItem('auth_user', JSON.stringify(this.user));
        },
        async login(credentials) {
            const { data } = await api.post('/auth/login', credentials);

            this.persistSession(data);

            return data;
        },
        async logout() {
            try {
                await api.post('/auth/logout');
            } catch {
                // Ignorar errores de red al cerrar sesión.
            }

            this.token = null;
            this.user = null;
            localStorage.removeItem('auth_token');
            localStorage.removeItem('auth_user');
        },
        clearSession() {
            this.token = null;
            this.user = null;
            localStorage.removeItem('auth_token');
            localStorage.removeItem('auth_user');
        },
    },
});
