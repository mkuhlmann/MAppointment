<script lang="ts" setup>
import { h, ref, inject, Component } from 'vue';
import { darkTheme, NConfigProvider, NCard, NLayout, NIcon, NText, NLayoutHeader, NMenu, NLayoutSider } from 'naive-ui';
import DashboardIcon from '@vicons/carbon/Dashboard';
import CalendarIcon from '@vicons/carbon/Calendar';
import UserIcon from '@vicons/carbon/User';
import { RouterLink, useRoute } from 'vue-router';


const $isDarkMode = inject<boolean>('$isDarkMode');
const useDarkMode = ($isDarkMode) ? darkTheme : null;
const step = ref(1);
const route = useRoute();

const renderIcon = function (icon: Component) {
	return () => h(NIcon, null, { default: () => h(icon) })
}

const renderMenuLabel = function (label: string, path: string) {
	return h(RouterLink, { to: { path: path } }, { default: () => label });
}

const menuOptions = [
	{
		label: () => renderMenuLabel('Dashboard', '/'),
		key: 'dashboard',
		icon: renderIcon(DashboardIcon)
	},
	{
		label: () => renderMenuLabel('Termine', '/appointments'),
		key: 'appointments',
		icon: renderIcon(CalendarIcon)
	},
	{
		label: () => 'Nutzer', //enderMenuLabel('Nutzer', '/users'),
		key: 'users',
		icon: renderIcon(UserIcon)
	}
];

</script>

<template>
	<n-config-provider :theme="useDarkMode">
		<n-layout position="absolute">
			<n-layout-header
				style="height: 64px; display: flex; align-items: center; padding: 0 32px;"
				bordered
			>
				<n-text class="logo">MAppointment</n-text>
				<div class="flex-grow"></div>
				<div>
					John Doe
				</div>
			</n-layout-header>
			<n-layout has-sider position="absolute" style="top: 64px;">
				<n-layout-sider
					v-show="route.path != '/login'"
					bordered
					collapse-mode="width"
					:collapsed-width="64"
					:width="240"
					show-trigger
				>
					<n-menu :collapsed-width="64" :collapsed-icon-size="22" :options="menuOptions" />
				</n-layout-sider>
				<n-layout v-bind:class="{ 'bg-warm-gray-100': !$isDarkMode }">
					<router-view></router-view>
				</n-layout>
			</n-layout>
		</n-layout>
	</n-config-provider>
</template>

<style scoped>
.logo {
	display: block;
	font-size: 20px;
	font-weight: bold;
}
</style>
