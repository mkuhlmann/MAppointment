<script setup lang="ts">
import { ref, inject, computed, onMounted } from 'vue'
import { NCard,  NDivider, NButton, NSpace, NSpin, NText, NP, NResult, NA, NTable } from 'naive-ui';
import { useRouter, useRoute, routerKey } from 'vue-router';
import dayjs from 'dayjs';
import { LMap, LTileLayer, LMarker, LPopup, LControlLayers } from '@vue-leaflet/vue-leaflet';
import 'leaflet/dist/leaflet.css';

import RenderMarkdown from '@/shared/components/RenderMarkdown.vue';

import { useApi } from '@/shared/composables/api';
import { Appointment } from '@/types';

const api = useApi();

const route = useRoute();
const router = useRouter();
const $isDarkMode = inject<boolean>('$isDarkMode');

const isLoading = ref(true);

const booking = ref();
const appointment = ref<Partial<Appointment>>({});
const slot = ref();

onMounted(async () => {
	try {
		if(route.query.c) {
			let confirmation = await api.$fetch(`/api/v1/bookings/${route.params.id}/confirm`, {
				method: 'POST',
				body: {
					secret: route.query.c
				}
			});
			router.push('/booking/' + route.params.id);
		}
		let _booking = await api.$fetch(`/api/v1/bookings/${route.params.id}`);
		booking.value = _booking;
		let _slot = await api.$fetch(`/api/v1/slots/${_booking.slotId}`);
		slot.value = _slot;
		let _appointment = await api.$fetch(`/api/v1/appointments/${_slot.appointmentId}`);
		appointment.value = _appointment;
	} catch (e) {
		console.log(e);
		console.log('error');
	}

	isLoading.value = false;
});
</script>

<template>
	<n-spin :show="isLoading">
		<n-card v-if="!isLoading" class="booking-container" :title="appointment.name" size="huge">
			<n-result
				v-if="appointment.requireMailValidation && !booking.mailConfirmedAt"
				class="my-5"
				status="warning"
				title="Bestätigung erforderlich"
				description="Bitte bestätigen Sie die Buchung über den Link den Sie per E-Mail erhalten haben." />

			<n-result
				v-if="!appointment.requireMailValidation || booking.mailConfirmedAt"
				status="success"
				title="Buchung bestätigt"
				description="Sie haben eine Bestätigung per E-Mail erhalten."
			></n-result>


			<n-divider />


			<render-markdown v-if="appointment.description" :markdown="appointment.description" />
			<div v-if="!appointment.description" class="mt-5"></div>

			

			<n-table>
				<tbody>
					<tr>
						<td>Vorname</td>
						<td>{{ booking.firstName }}</td>
					</tr>
					<tr>
						<td>Nachname</td>
						<td>{{ booking.lastName }}</td>
					</tr>
					<tr>
						<td>E-Mail</td>
						<td>{{ booking.email }}</td>
					</tr>
					<tr>
						<td>Termin</td>
						<td>{{ dayjs.utc(slot.start).local().format('dddd, DD.MM.YYYY') }}</td>
					</tr>
					<tr>
						<td>Zeit</td>
						<td>{{ dayjs.utc(slot.start).local().format('HH:mm') }} &mdash; {{ dayjs.utc(slot.end).local().format('HH:mm') }}</td>
					</tr>
				</tbody>
			</n-table>

			<div v-if="appointment.latitude" class="w-full mt-5 rounded-md" style="height: 200px;">
				<l-map :center="[appointment.latitude, appointment.longitude]" :zoom="13">
					<l-tile-layer url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"></l-tile-layer>
					<l-control-layers />
					<l-marker :lat-lng="[appointment.latitude, appointment.longitude]">
						<l-popup>Veranstaltungsort</l-popup>
					</l-marker>
				</l-map>
			</div>

			<n-divider />

			<n-space justify="space-between">
				<n-text depth="3">Datenschutz &mdash; Impressum</n-text>
				<n-text depth="3">Realisiert durch MAppointment</n-text>
			</n-space>
		</n-card>
	</n-spin>
</template>

<style scoped>
.booking-container {
	width: 100vw;
	max-width: 600px;
}

@media (min-width: 768px) {
	.booking-container {
		width: 80vw;
	}
}

@media (min-width: 992px) {
	.booking-container {
		width: 60vw;
	}
}
</style>
