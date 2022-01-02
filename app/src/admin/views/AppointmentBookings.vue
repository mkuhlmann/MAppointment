<script lang="ts" setup>
import { useApi } from '@/shared/composables/api';
import { DataTableColumns, NDataTable, NButton, NIcon } from 'naive-ui';
import DocumentDownloadIcon from '@vicons/carbon/DocumentDownload'
import { ref } from 'vue';
import { useRoute } from 'vue-router';
import { Booking, Slot } from '@/types';

const api = useApi();
const route = useRoute();

const bookings = ref<(Partial<Slot> & Booking)[]>([]);

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

const downloadCsv = function() {
	let csv = 'Terminslot;Vorname;Nachname;E-Mail;Buchungszeit\n';

	bookings.value.forEach(booking => {
		csv += `${booking.start};${booking.firstName};${booking.lastName};${booking.email};${booking.createdAt}\n`;
	});
	
	const blob = new Blob([csv], { type: 'text/csv' });

	const url = window.URL.createObjectURL(blob);
	const link = document.createElement('a');
	link.href = url;
	link.setAttribute('download', 'Buchungen.csv');
	document.body.appendChild(link);
	link.click();
	document.body.removeChild(link);
};

</script>


<template>
	<div class="mb-5">
		<n-button type="info" @click="downloadCsv">
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