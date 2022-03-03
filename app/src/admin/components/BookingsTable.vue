<script lang="ts" setup>
import { NIcon, NButton, NDataTable, DataTableColumns, NH2, useMessage } from 'naive-ui';
import { ref, h } from 'vue';
import DocumentDownloadIcon from '@vicons/carbon/DocumentDownload';
import { Slot, Booking } from '@/types';
import { useApi } from '@/shared/composables/api';

const message = useMessage();

const props = defineProps<{ 
	appointmentId?: string,
	slotId?: string
 }>();


const emits = defineEmits<{ 
	(event: 'close'): void
}>();


const api = useApi();

const bookings = ref<(Partial<Slot> & Booking)[]>([]);

const fetchBookings = async () => {
	console.log(props);
	if(props.appointmentId) {
		bookings.value = await api.$fetch('/api/v1/appointments/' + props.appointmentId + '/bookings');
	} else if(props.slotId) {
		bookings.value = await api.$fetch('/api/v1/slots/' + props.slotId + '/bookings');
	}
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