export function authGuard(to, from, next) {
    const token = localStorage.getItem('auth_token');

    if (to.meta.requiresAuth && ! token) {
        next({ name: 'login' });

        return;
    }

    if (to.meta.guest && token) {
        next({ name: 'home' });

        return;
    }

    next();
}
