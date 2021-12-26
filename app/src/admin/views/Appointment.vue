<script lang="ts" setup>
import { NMenu } from 'naive-ui';

const menuOptions = [
	{
		label: 'Allgemein',
		key: 'general'
	}, {
		label: 'Termine',
		key: 'slots'
	}, {
		label: 'Buchungen',
		key: 'bookings'
	}
	
];

</script>

<template>
	<n-menu :options="menuOptions" mode="horizontal" />

	<router-view></router-view>
</template>