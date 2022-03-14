<script lang="ts" setup>
import { h, ref, inject, Component, computed } from 'vue';
import { darkTheme, NConfigProvider, NMessageProvider, NLayout, NIcon, NText, NLayoutHeader, NMenu, NLayoutSider, NButton } from 'naive-ui';
import { RouterLink, useRoute, useRouter } from 'vue-router';
import { Key as MenuKey } from 'naive-ui/lib/menu/src/interface';
import { useUser } from './composables/user';
import { useApi } from '@/shared/composables/api';
import { useDark, useToggle } from '@vueuse/core';

import DashboardIcon from '@vicons/carbon/Dashboard';
import CalendarIcon from '@vicons/carbon/Calendar';
import LogoutIcon from '@vicons/carbon/Logout';
import MoonIcon from '@vicons/carbon/Moon';
import SunIcon from '@vicons/carbon/Sun';

const step = ref(1);
const route = useRoute();
const router = useRouter();
const api = useApi();

const isDark = useDark();
const toggleDark = useToggle(isDark);
const useDarkMode = computed(() => {
	if(isDark.value) {
		return darkTheme;
	}
	return null;
});

const renderIcon = function (icon: Component) {
	return () => h(NIcon, null, { default: () => h(icon) })
}

const renderMenuLabel = function (label: string, path: string) {
	return h(RouterLink, { to: { path: path } }, { default: () => label });
}

const user = useUser();
const logout = async function () {
	api.logout();
	user.value = null;
	router.push('/login');
};

const activeKey = ref(route.name as MenuKey);

const menuOptions = [
	{
		label: () => renderMenuLabel('Dashboard', '/'),
		key: 'Dashboard',
		icon: renderIcon(DashboardIcon)
	},
	{
		label: () => renderMenuLabel('Veranstaltungen', '/appointments'),
		key: 'Events',
		icon: renderIcon(CalendarIcon)
	}
];

</script>

<template>
	<n-config-provider :theme="useDarkMode">
		<n-message-provider :duration="4000" :keep-alive-on-hover="true">
			<n-layout position="absolute">
				<n-layout-header
					style="height: 64px; display: flex; align-items: center; padding: 0 32px;"
					bordered
				>
					<n-text class="logo">MAppointment</n-text>
					<div class="flex-grow"></div>
					<n-button @click="toggleDark()">
						<template #icon>
							<n-icon>
								<MoonIcon v-if="!isDark" />
								<SunIcon v-if="isDark" />
							</n-icon>
						</template>
					</n-button>
					
					<div class="mx-5">{{ user?.username }}</div>
					<n-button v-if="user" @click="logout">
						<template #icon>
							<n-icon>
								<LogoutIcon />
							</n-icon>
						</template>
						Abmelden
					</n-button>
					
				</n-layout-header>
				<n-layout has-sider position="absolute" style="top: 64px;">
					<n-layout-sider
						v-if="route.path != '/login'"
						bordered
						collapse-mode="width"
						:collapsed-width="64"
						:width="240"
						show-trigger
					>
						<n-menu v-model:value="activeKey" :collapsed-width="64" :collapsed-icon-size="22" :options="menuOptions" />
					</n-layout-sider>
					<n-layout v-bind:class="{ 'bg-warm-gray-100': !isDark }">
						<router-view></router-view>
					</n-layout>
				</n-layout>
			</n-layout>
		</n-message-provider>
	</n-config-provider>
</template>

<style>
.logo {
	display: block;
	font-size: 20px;
	font-weight: bold;
}

.link {
	/** @ignore */
	@apply text-teal-300 hover:text-teal-500 ease-in-out transition duration-200 cursor-pointer;
}
</style>
