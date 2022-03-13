<script lang="ts" setup>
import { NMenu, NHr, NIcon, NButton } from 'naive-ui';
import { h, ref } from 'vue';
import { RouterLink, useRoute } from 'vue-router';
import PersonIcon from '@vicons/carbon/Person';
import EventScheduleIcon from '@vicons/carbon/EventSchedule';
import EditIcon from '@vicons/carbon/Edit';
import LinkIcon from '@vicons/carbon/Link';
import { Key as MenuKey } from 'naive-ui/lib/menu/src/interface';


const route = useRoute();

const activeKey = ref(route.name as MenuKey);

const menuOptions = [
	{
		label: () => h(RouterLink, { to: { path: `/appointments/${route.params.id}` } }, { default: () => 'Übersicht' }),
		key: 'EventOverview',
		icon:  () => h(NIcon, null, { default: () => h(EditIcon) })
	},
	{
		label: () => h(RouterLink, { to: { path: `/appointments/${route.params.id}/slots` } }, { default: () => 'Termine' }),
		key: 'EventSlots',
		icon:  () => h(NIcon, null, { default: () => h(EventScheduleIcon) })
	},
	{
		label: () => h(RouterLink, { to: { path: `/appointments/${route.params.id}/bookings` } }, { default: () => 'Buchungen' }),
		key: 'EventBookings',
		icon:  () => h(NIcon, null, { default: () => h(PersonIcon) })
	}
];

</script>

<template>
	<div class="flex flex-col h-full">
		<div class="flex mt-3 items-center">		
			<n-menu class="flex-grow" v-model:value="activeKey" :options="menuOptions" mode="horizontal" />
			<n-button tag="a" :href="'/' + route.params.id" target="_blank" class="mr-5" type="primary">
				<template #icon>
					<n-icon>
						<LinkIcon />
					</n-icon>
				</template>
				Buchungslink
			</n-button>
		</div>

		<n-hr />

		<div class="p-5 pt-1 flex-grow">
			<router-view />
		</div>
	</div>
</template>