<script lang="ts" setup>
import { h, ref, inject, Component } from 'vue';
import { darkTheme, NConfigProvider, NCard, NLayout, NIcon, NSteps, NLayoutHeader, NMenu, NLayoutSider } from 'naive-ui';
import { Dashboard as DashboardIcon } from '@vicons/carbon';

const $isDarkMode = true || inject<boolean>('$isDarkMode');
const useDarkMode = ($isDarkMode) ? darkTheme : null;
const step = ref(1);

const renderIcon = function(icon: Component) {
  return () => h(NIcon, null, { default: () => h(icon) })
}

const menuOptions = [
	{
		label: 'Dashboard',
		key: 'hear-the-wind-sing',
		icon: renderIcon(DashboardIcon)
	}
];


</script>

<template>
	<n-config-provider :theme="useDarkMode">
		<n-layout position="absolute">
			<n-layout-header style="height: 64px; display: flex; align-items: center; padding: 0 32px;" bordered>
				<n-text class="logo">MAppointments</n-text>
			</n-layout-header>
			<n-layout has-sider position="absolute" style="top: 64px;">
				<n-layout-sider bordered collapse-mode="width" :collapsed-width="64" :width="240" show-trigger>
					<n-menu :collapsed-width="64" :collapsed-icon-size="22" :options="menuOptions" />
				</n-layout-sider>
				<n-layout>
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
