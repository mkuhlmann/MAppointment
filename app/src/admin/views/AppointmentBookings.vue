<script lang="ts" setup>
import { useApi } from '@/shared/composables/api';
import { DataTableColumns, NDataTable, NButton, NIcon } from 'naive-ui';
import DocumentDownloadIcon from '@vicons/carbon/DocumentDownload'
import { ref } from 'vue';
import { useRoute } from 'vue-router';

const api = useApi();
const route = useRoute();

const bookings = ref([]);

api.$fetch(`/api/v1/appointments/${route.params.id}/bookings`).then(res => {
	bookings.value = res;
});

const bookingsColumns = [
	{
		title: 'Termin',
		key: 'start'
	},
	{
		title: 'Vorname',
		key: 'firstName'
	},
	{
		title: 'Nachname',
		key: 'lastName'
	},
	{
		title: 'E-Mail',
		key: 'email'
	},
	{
		title: 'Buchungszeit',
		key: 'createdAt'
	}
] as DataTableColumns;

</script>


<template>
	<div class="mb-5">
		<n-button type="info">
			<template #icon>
				<n-icon>
					<DocumentDownloadIcon />
				</n-icon>
			</template>
			.CSV
		</n-button>
	</div>

	<n-data-table :columns="bookingsColumns" :data="bookings" :pagination="false" />
</template>