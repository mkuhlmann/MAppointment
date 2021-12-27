<script lang="ts" setup>
import { NMenu, NHr } from 'naive-ui';
import { h } from 'vue';
import { RouterLink, useRoute } from 'vue-router';

const route = useRoute();

const menuOptions = [
	{
		label: () => h(RouterLink, { to: { path: `/appointments/${route.params.id}` } }, { default: () => 'Übersicht' }),
		key: 'general'
	},
	{
		label: () => h(RouterLink, { to: { path: `/appointments/${route.params.id}/slots` } }, { default: () => 'Termine' }),
		key: 'slots'
	},
	{
		label: () => h(RouterLink, { to: { path: `/appointments/${route.params.id}/bookings` } }, { default: () => 'Buchungen' }),
		key: 'bookings'
	}

];

</script>

<template>
	<n-menu class="mt-2" :options="menuOptions" mode="horizontal" />
	<n-hr />

	<div class="p-5">
		<router-view />
	</div>
</template>