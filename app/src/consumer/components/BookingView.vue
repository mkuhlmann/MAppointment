<script setup lang="ts">
import { ref, toRefs, inject, computed } from 'vue'
import { NCard, NSteps, NStep, NForm, NFormItem, NInput, FormRules, NDivider, NButton, NSpace, NSpin, NText, NP, NH2, NResult, NA, NTable } from 'naive-ui';
import { Calendar } from 'v-calendar';
import { useRouter, useRoute } from 'vue-router';
import dayjs from 'dayjs';
import { LMap, LTileLayer, LMarker, LPopup, LControlLayers } from '@vue-leaflet/vue-leaflet';
import 'leaflet/dist/leaflet.css';

import { useApi } from '@/shared/composables/api';
import { Appointment } from '@/types';

const api = useApi();

const route = useRoute();
const router = useRouter();

const $isMobileScreen = inject<boolean>('$isMobileScreen');
const $isDarkMode = inject<boolean>('$isDarkMode');

const alert = ref('success')
const isLoading = ref(true);

const booking = ref({
	firstname: 'a',
	lastname: 'b',
	email: 'a@a.de',
	comment: ''
});

const appointment = ref<Partial<Appointment>>({});
const slot = ref();

let data = api.$fetch(`/api/v1/bookings/${route.params.id}`);

</script>

<template>
	<n-spin :show="isLoading">
		<n-card v-if="!isLoading" class="booking-container" :title="appointment.name" size="large">
			<n-p>{{ appointment.description }}</n-p>

			
				<n-result v-if="alert == 'sucess'"
					status="success"
					title="Termin gebucht"
					description="Sie erhalten eine Bestätigung per E-Mail."
				></n-result>

				<n-table>
					<tbody>
						<tr>
							<td>Vorname</td>
							<td>{{ booking.firstname }}</td>
						</tr>
						<tr>
							<td>Nachname</td>
							<td>{{ booking.lastname }}</td>
						</tr>
						<tr>
							<td>E-Mail</td>
							<td>{{ booking.email }}</td>
						</tr>
						<tr>
							<td>Termin</td>
							<td>{{ dayjs(slot.date).format('dddd, DD.MM.YYYY') }}</td>
						</tr>
						<tr>
							<td>Zeit</td>
							<td>{{ dayjs(slot.date).format('HH:mm') }} &mdash; {{ dayjs(slot.date).add(slot.duration, 'minute').format('HH:mm') }}</td>
						</tr>
					</tbody>
				</n-table>
				<div v-if="appointment.locationLat" class="w-full mt-5 rounded-md" style="height: 200px;">
					<l-map :center="[appointment.locationLat, appointment.locationLng]" :zoom="13">
						<l-tile-layer url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"></l-tile-layer>
						<l-control-layers />
						<l-marker :lat-lng="[appointment.locationLat, appointment.locationLng]">
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
.vc-container.vc-is-dark {
	background-color: rgb(16, 16, 20);
	border-color: rgba(255, 255, 255, 0.09);
}
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
