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

const step = ref(1);
const isLoading = ref(true);
const slotsLoading = ref(false);

const booking = ref({
	firstname: 'a',
	lastname: 'b',
	email: 'a@a.de',
	comment: ''
});

const bookingFormRef = ref<HTMLFormElement>();

const submitBookingForm = function () {
	bookingFormRef.value!.validate(function (errors: any) {
		if (!errors) {
			step.value++;
		}
	});
};

const formRules: FormRules = {
	firstname: {
		required: true,
		trigger: ['input', 'blur'],
		message: 'Bitte Vornamen angeben.'
	},
	lastname: {
		required: true,
		trigger: ['input', 'blur'],
		message: 'Bitte Nachnamen angeben.'
	},
	email: {
		required: true,
		validator: function (rule, value) {
			if (!/^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/.test(value)) {
				return new Error('Bitte gültige E-Mail angeben');
			}
			return true;
		},
		trigger: ['input', 'blur']
	}
};


const appointment = ref<Partial<Appointment>>({});
const availableDates = ref<Date[]>([]);

let appointmentData = api.$fetch('/api/v1/appointments/' + route.params.id, { throwError: true })
	.then(async _appointment => {
		appointment.value = _appointment;

		let _appointmentDates = await api.$fetch('/api/v1/appointments/' + route.params.id + '/get-available-dates');

		let _dates = [];
		for (let date of _appointmentDates) {
			_dates.push(dayjs(date.date).toDate());
		}
		availableDates.value = _dates;
		isLoading.value = false;
	})
	.catch(response => {
		router.push('/404');
		return response;
	});


const availableSlots = ref<{ id: string, free: string, duration: number, date: string }[]>([]);
const chosenSlot = ref<{ id: string, free: string, duration: number, date: string }>({ id: '', free: '', duration: 0, date: '' });

const chosenDate = ref('');

const chooseDate = async function (date: { id: string }) {
	slotsLoading.value = true;
	chosenDate.value = date.id;
	let response = await fetch('http://localhost:8080/api/v1/appointments/' + appointment.value.id + '/get-available-slots/' + date.id);
	slotsLoading.value = false;
	availableSlots.value = await response.json();
	chosenSlot.value.id = '';
};


const submitBooking = async function () {
	isLoading.value = true;

	let response = await api.$fetch(`/api/v1/bookings`, {
		method: 'POST',
		body: {
			appointmentId: appointment.value.id,
			slotId: chosenSlot.value.id,

			firstname: booking.value.firstname,
			lastname: booking.value.lastname,
			email: booking.value.email,
			comment: booking.value.comment
		}
	});

	console.log(response);
	if (response.success) {
		router.push(`/booking/${response.bookingId}?e=s`);
	}

	isLoading.value = false;

};

</script>

<template>
	<n-spin :show="isLoading">
		<n-card v-if="!isLoading" class="booking-container" :title="appointment.name" size="large">
			<n-p>{{ appointment.description }}</n-p>

			<div v-if="step == 1">
				<Calendar
					:available-dates="availableDates"
					:is-expanded="true"
					:is-dark="$isDarkMode"
					v-on:dayclick="chooseDate"
					color="gray"
				/>

				<n-h2>
					Verfügbare Termine
					<span
						v-if="availableSlots.length > 0"
					>am {{ dayjs(chosenDate).format('dddd, DD.MM.YYYY') }}</span>
				</n-h2>

				<n-p v-if="availableSlots.length == 0">Bitte Datum auswählen.</n-p>

				<n-space v-if="!slotsLoading">
					<n-button
						v-for="slot in availableSlots"
						:key="slot.id"
						:type="chosenSlot.id == slot.id ? 'success' : 'default'"
						v-on:click="chosenSlot = slot"
					>{{ dayjs(slot.date).format('HH:mm') }} &mdash; {{ dayjs(slot.date).add(slot.duration, 'minute').format('HH:mm') }}</n-button>
				</n-space>

				<n-spin size="large" v-if="slotsLoading" />

				<n-button
					class="mt-5 mb-1 w-full"
					size="large"
					type="info"
					v-on:click="step++"
					:disabled="chosenSlot.id.length == 0"
				>Weiter</n-button>
			</div>

			<div v-if="step == 2">
				<n-table class="mb-5">
					<tbody>
						<tr>
							<td>Termin</td>
							<td>{{ dayjs(chosenSlot.date).format('dddd, DD.MM.YYYY') }}</td>
						</tr>
						<tr>
							<td>Zeit</td>
							<td>{{ dayjs(chosenSlot.date).format('HH:mm') }} &mdash; {{ dayjs(chosenSlot.date).add(chosenSlot.duration, 'minute').format('HH:mm') }}</td>
						</tr>
					</tbody>
				</n-table>

				<n-form :model="booking" ref="bookingFormRef" :rules="formRules">
					<n-form-item item path="firstname" label="Vorname">
						<n-input v-model:value="booking.firstname" placeholder="Vorname ..." />
					</n-form-item>
					<n-form-item item path="lastname" label="Nachname">
						<n-input v-model:value="booking.lastname" placeholder="Nachname ..." />
					</n-form-item>
					<n-form-item item path="email" label="E-Mail">
						<n-input v-model:value="booking.email" placeholder="E-Mail ..." />
					</n-form-item>

					<n-form-item v-if="appointment.canComment" item path="comment" label="Anmerkung">
						<n-input type="textarea" v-model:value="booking.comment" placeholder="Anmerkung ..." />
					</n-form-item>
				</n-form>

				<n-button
					class="mt-3 mb-1 w-full"
					size="large"
					type="info"
					v-on:click="submitBookingForm"
				>Weiter</n-button>

				<n-a class="mt-2" v-on:click="step--">Zurück</n-a>
			</div>

			<div v-if="step == 3">
				<n-h2>Buchungsübersicht</n-h2>

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
							<td>{{ dayjs(chosenSlot.date).format('dddd, DD.MM.YYYY') }}</td>
						</tr>
						<tr>
							<td>Zeit</td>
							<td>{{ dayjs(chosenSlot.date).format('HH:mm') }} &mdash; {{ dayjs(chosenSlot.date).add(chosenSlot.duration, 'minute').format('HH:mm') }}</td>
						</tr>
					</tbody>
				</n-table>

				<n-button
					class="mt-5 mb-1 w-full"
					size="large"
					type="info"
					v-on:click="submitBooking"
				>Jetzt verbindlich buchen.</n-button>

				<n-a class="mt-2" v-on:click="step--">Zurück</n-a>
			</div>

			<n-divider />

			<n-steps :vertical="$isMobileScreen" :current="step">
				<n-step title="Datum & Zeit" description="auswählen" />
				<n-step title="Kontaktdaten" description="angeben" />
				<n-step title="Bestätitung" description="erhalten" />
			</n-steps>

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
