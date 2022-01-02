import { createApp } from 'vue'
import App from './App.vue'
import { createRouter, createWebHistory } from 'vue-router';
import dayjs from 'dayjs';
import utc from 'dayjs/plugin/utc';
import 'dayjs/locale/de';
import 'vfonts/Lato.css'
import 'vfonts/FiraCode.css'

import 'virtual:windi.css';

import IndexComponent from './components/Index.vue';
import BookingComponent from './components/Booking.vue';

const routes = [
	{
		path: '/404',
		name: 'NotFound',
		component: IndexComponent
	},
	{
		path: '/booking/:id',
		component: () => import('./components/BookingView.vue')
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
dayjs.extend(utc);

const app = createApp(App);

app.provide('$isMobileScreen', screen.width <= 760);
app.provide('$isDarkMode', window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches);

app
	.use(createRouter({
		history: createWebHistory('/'),
		routes,
	}))
	.mount('#app');

