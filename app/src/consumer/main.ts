import { createApp } from 'vue'
import App from './App.vue'
import { createRouter, createWebHistory } from 'vue-router';
import dayjs from 'dayjs';
import 'dayjs/locale/de';
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

dayjs.locale('de');

const app = createApp(App);
app
	.use(createRouter({
		history: createWebHistory((import.meta.env.MODE == 'development') ? '/app/' : '/'),
		routes,
	}))
	.mount('#app');

app.provide('$isMobileScreen', screen.width <= 760);
app.provide('$isDarkMode', window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches);