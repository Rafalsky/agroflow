import './styles/admin.css';
import { createApp } from 'vue';
import AdminWeek from './vue/AdminWeek.vue';

// Basic Vue setup for admin widgets
document.addEventListener('DOMContentLoaded', () => {
    const adminWeekEl = document.getElementById('vue-admin-week');
    if (adminWeekEl) {
        createApp(AdminWeek).mount(adminWeekEl);
    }
});
