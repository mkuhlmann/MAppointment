<script lang="ts" setup>
import { useApi } from '@/shared/composables/api';
import { DataTableColumns, NDataTable, NButton, NIcon } from 'naive-ui';
import DocumentDownloadIcon from '@vicons/carbon/DocumentDownload'
import { ref,h } from 'vue';
import { useRoute } from 'vue-router';
import { Booking, Slot } from '@/types';

const api = useApi();
const route = useRoute();

const bookings = ref<(Partial<Slot> & Booking)[]>([]);

const fetchBookings = async () => {
	let _bookings = await api.$fetch(`/api/v1/appointments/${route.params.id}/bookings`);
	bookings.value = _bookings;
};

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
	},
	{
		title: 'E-Mail versandt',
		key: 'emailSent'
	},
	{
		title: 'E-Mail bestätigt',
		key: 'emailConfirmed'
	},
	{
		title: 'Aktionen',
		key: 'actions',
		render: (row) => {
			return h(NButton, {
				size: 'small', onClick: () => {
					api.$fetch(`/api/v1/bookings/${row.id}/resend-mail`).then(() => fetchBookings());
				}
			}, { default: () => 'E-Mail erneut senden' });
		}
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

fetchBookings().then();

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