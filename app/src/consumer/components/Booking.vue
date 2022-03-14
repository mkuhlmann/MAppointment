<script setup lang="ts">
import { ref, toRefs, inject, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router';
import { useDark, useNavigatorLanguage, useTitle } from '@vueuse/core';

import { NCard, NSteps, NStep, NForm, NFormItem, NInput, FormRules, NDivider, NButton, NSpace, NSpin, NText, NP, NH2, NResult, NA, NTable, NAlert } from 'naive-ui';

import { Calendar } from 'v-calendar';
import 'v-calendar/dist/style.css';

import dayjs from 'dayjs';
import dayjsTz from 'dayjs/plugin/timezone';

import { LMap, LTileLayer, LMarker, LPopup, LControlLayers } from '@vue-leaflet/vue-leaflet';
import 'leaflet/dist/leaflet.css';

import RenderMarkdown from '@/shared/components/RenderMarkdown.vue';

import { useApi } from '@/shared/composables/api';
import { Appointment } from '@/types';

dayjs.extend(dayjsTz);

const windowTitle = useTitle();
const api = useApi();

const route = useRoute();
const router = useRouter();
const $isMobileScreen = inject<boolean>('$isMobileScreen');

const isDark = useDark();
const navigatorLanguage = useNavigatorLanguage();

const step = ref(1);
const isLoading = ref(true);
const slotsLoading = ref(false);

const booking = ref({
	firstName: '',
	lastName: '',
	phone: '',
	email: '',
	comment: '',
	timezone: dayjs.tz.guess(),
	language: navigatorLanguage.language.value
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
	firstName: {
		required: true,
		trigger: ['input', 'blur'],
		message: 'Bitte Vornamen angeben.'
	},
	lastName: {
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
	},
	phone: {
		required: false,
		validator: function (rule, value) {
			if ((appointment.value.requirePhoneNumber || value.trim().length > 0) && !/^(?:(?:\(?(?:00|\+)([1-4]\d\d|[1-9]\d+)\)?)[\-\.\ \\\/]?)?((?:\(?\d{1,}\)?[\-\.\ \\\/]?){0,})(?:[\-\.\ \\\/]?(?:#|ext\.?|extension|x)[\-\.\ \\\/]?(\d+))?$/i.test(value)) {
				return new Error('Bitte gültige Telefonnummer angeben');
			}
			return true;
		},
		trigger: ['input', 'blur']
	},
};


const appointment = ref<Partial<Appointment>>({});
const availableDates = ref<Date[]>([]);

let appointmentData = api.$fetch('/api/v1/appointments/' + route.params.id, { throwError: true })
	.then(async _appointment => {
		appointment.value = _appointment;

		if (!_appointment.isActive) {
			step.value = -1;
			isLoading.value = false;
			return;
		}

		windowTitle.value = `${_appointment.name} – Terminbuchung`;

		let _appointmentDates = await api.$fetch('/api/v1/appointments/' + route.params.id + '/available-dates');

		let _dates = [];
		for (let date of _appointmentDates) {
			_dates.push(dayjs.utc(date.start).toDate());
		}
		availableDates.value = _dates;
		isLoading.value = false;
	})
	.catch(response => {
		router.push('/404');
		return response;
	});


const availableSlots = ref<{ id: string, free: string, start: string, end: string }[]>([]);
const chosenSlot = ref<{ id: string, free: string, start: string, end: string }>({ id: '', free: '', start: '', end: '' });

const chosenDate = ref('');

const chooseDate = async function (date: { id: string }) {
	slotsLoading.value = true;
	chosenDate.value = date.id;
	availableSlots.value = await api.$fetch(`/api/v1/appointments/${appointment.value.id}/available-slots/${date.id}`);
	slotsLoading.value = false;
	chosenSlot.value.id = '';
};

const bookingResponse = ref({ error: null });
const submitBooking = async function () {
	isLoading.value = true;

	let response = await api.$fetch(`/api/v1/bookings`, {
		method: 'POST',
		body: {
			appointmentId: appointment.value.id,
			slotId: chosenSlot.value.id,

			firstName: booking.value.firstName,
			lastName: booking.value.lastName,
			email: booking.value.email,
			phone: booking.value.phone,
			comment: booking.value.comment,

			timezone: booking.value.timezone,
			language: booking.value.language
		}
	});

	if (response.success) {
		router.push(`/booking/${response.bookingId}?e=s`);
	} else {
		isLoading.value = false;
	}

	bookingResponse.value = response;
};

</script>

<template>
	<n-spin :show="isLoading">
		<n-card
			v-if="!isLoading"
			class="booking-container sm:border-0"
			:title="appointment.name"
			size="large"
		>
			<RenderMarkdown v-if="appointment.description" :markdown="appointment.description" class="mt-5" />
			
			<div class="mt-5"></div>

			<div v-if="step == -1">
				<n-alert type="info">Buchungen aktuell nicht möglich.</n-alert>
			</div>

			<div v-if="step == 1">
				<n-table class="mb-5" v-if="appointment.location">
					<tbody>
						<tr v-if="appointment.location">
							<td>Veranstaltungsort</td>
							<td>{{ appointment.location }}</td>
						</tr>
					</tbody>
				</n-table>
				<calendar
					:available-dates="availableDates"
					:is-expanded="true"
					:is-dark="isDark"
					v-on:dayclick="chooseDate"
					color="gray"
				/>

				<n-h2>
					Verfügbare Termine
					<span
						v-if="availableSlots.length > 0"
					>am {{ dayjs.utc(chosenDate).local().format('dddd, DD.MM.YYYY') }}</span>
				</n-h2>

				<n-p v-if="availableSlots.length == 0">Bitte Datum auswählen.</n-p>

				<n-space v-if="!slotsLoading">
					<n-button
						v-for="slot in availableSlots"
						:key="slot.id"
						:type="chosenSlot.id == slot.id ? 'success' : 'default'"
						v-on:click="chosenSlot = slot"
					>{{ dayjs.utc(slot.start).local().format('HH:mm') }} &mdash; {{ dayjs.utc(slot.end).local().format('HH:mm') }}</n-button>
				</n-space>

				<n-spin size="large" v-if="slotsLoading" />

				<n-button
					class="mt-5 mb-1 w-full"
					size="large"
					type="info"
					v-on:click="step = 2"
					:disabled="chosenSlot.id.length == 0"
				>Weiter</n-button>
			</div>

			<div v-if="step == 2">
				<n-table class="mb-5">
					<tbody>
						<tr v-if="appointment.location">
							<td>Veranstaltungsort</td>
							<td>{{ appointment.location }}</td>
						</tr>
						<tr>
							<td>Termin</td>
							<td>{{ dayjs.utc(chosenSlot.start).local().format('dddd, DD.MM.YYYY') }}</td>
						</tr>
						<tr>
							<td>Zeit</td>
							<td>{{ dayjs.utc(chosenSlot.start).local().format('HH:mm') }} &mdash; {{ dayjs.utc(chosenSlot.end).local().format('HH:mm') }}</td>
						</tr>
					</tbody>
				</n-table>

				<n-form :model="booking" ref="bookingFormRef" :rules="formRules">
					<n-form-item item path="firstName" label="Vorname">
						<n-input v-model:value="booking.firstName" placeholder="Vorname ..." />
					</n-form-item>
					<n-form-item item path="lastName" label="Nachname">
						<n-input v-model:value="booking.lastName" placeholder="Nachname ..." />
					</n-form-item>
					<n-form-item item path="email" label="E-Mail">
						<n-input v-model:value="booking.email" placeholder="E-Mail ..." />
					</n-form-item>
					<n-form-item item path="phone" label="Telefon">
						<n-input v-model:value="booking.phone" placeholder="Telefon ..." />
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

				<n-a class="mt-2" v-on:click="step = 1">Zurück</n-a>
			</div>

			<div v-if="step == 3">
				<n-h2>Buchungsübersicht</n-h2>

				<n-alert
					v-if="bookingResponse.error"
					type="error"
					title="Buchung fehlgeschlagen"
					class="mb-5"
				>{{ bookingResponse.error }}</n-alert>

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
						<tr v-if="booking.phone.length > 0">
							<td>Telefon</td>
							<td>{{ booking.phone }}</td>
						</tr>
						<tr>
							<td>Termin</td>
							<td>{{ dayjs.utc(chosenSlot.start).local().format('dddd, DD.MM.YYYY') }}</td>
						</tr>
						<tr>
							<td>Zeit</td>
							<td>{{ dayjs.utc(chosenSlot.start).local().format('HH:mm') }} &mdash; {{ dayjs.utc(chosenSlot.end).local().format('HH:mm') }}</td>
						</tr>
						<tr>
							<td>Kommentar</td>
							<td>{{ booking.comment }}</td>
						</tr>
					</tbody>
				</n-table>

				<n-button
					class="mt-5 mb-1 w-full"
					size="large"
					type="info"
					v-on:click="submitBooking"
				>Jetzt verbindlich buchen.</n-button>

				<n-a class="mt-2" v-on:click="step = 2; bookingResponse.error = null;">Zurück</n-a>
			</div>

			<n-divider />

			<n-steps :vertical="$isMobileScreen" :current="step">
				<n-step title="Datum & Zeit" description="auswählen" />
				<n-step title="Kontaktdaten" description="angeben" />
				<n-step title="Buchung" description="bestätigen" />
			</n-steps>

			<n-divider />

			<n-space justify="space-between">
				<n-text depth="3"></n-text>
				<n-text depth="3">
					Realisiert durch
					<n-a href="https://github.com/mkuhlmann/MAppointment" target="_blank">MAppointment</n-a>
				</n-text>
			</n-space>
		</n-card>
	</n-spin>
</template>
