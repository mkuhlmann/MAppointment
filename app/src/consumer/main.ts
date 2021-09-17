import { createApp } from 'vue'
import App from './App.vue'
import { createRouter, createWebHistory } from 'vue-router';
import 'vfonts/Lato.css'
import 'vfonts/FiraCode.css'

import IndexComponent from './components/Index.vue';
import BookingComponent from './components/Booking.vue';
const routes = [
    {
        path: '/404',
        name: 'NotFound',
        component: IndexComponent
    },
    {
        path: '/:id',
        component: BookingComponent
    },
    {
        path: '/:pathMatch(.*)*',
        name: 'CatchAny',
        component: IndexComponent
    }
];

createApp(App)
    .use(createRouter({
        history: createWebHistory((import.meta.env.MODE == 'development') ? '/app/' : '/'),
        routes,
    }))
    .mount('#app');
