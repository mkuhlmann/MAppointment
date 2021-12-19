<script setup lang="ts">
import { ref, toRefs, inject, computed } from 'vue'
import { NCard, NSteps, NStep, NForm, NFormItem, NInput, FormRules, NDivider, NButton, NSpace, NSpin, NText, NP, NH2, NResult, NA, NTable } from 'naive-ui';
import { Calendar } from 'v-calendar';
import { useRouter, useRoute } from 'vue-router';
import dayjs from 'dayjs';
import { LMap, LTileLayer, LMarker, LPopup } from '@vue-leaflet/vue-leaflet';
import 'leaflet/dist/leaflet.css';

import { useApi } from '@/composables/api';
import { Appointment } from '@/types';

const api = useApi();

const route = useRoute();
const router = useRouter();
const $isMobileScreen = inject<boolean>('$isMobileScreen');
const $isDarkMode = inject<boolean>('$isDarkMode');

const step = ref(1);
const isLoading = ref(true);

const contact = ref({
	firstname: 'a',
	lastname: 'b',
	email: 'a@a.de',
	comment: ''
});

const contactFormRef = ref<HTMLFormElement>();

const submitContact = function() {
	contactFormRef.value!.validate(function(errors: any) {
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

fetch('http://localhost:8080/api/v1/appointments/' + route.params.id)
	.catch(response => {
		router.push('/404');
		return response;
	})
	.then(response => {
		if (response.status != 200) {
			router.push('/404');
			return;
		}
		else return response.json();
	})
	.then(json => {
		appointment.value = json;

		fetch('http://localhost:8080/api/v1/appointments/' + route.params.id + '/get-available-dates')
			.then(response => response.json())
			.then(json => {
				let _dates = [];
				for (let date of json) {
					_dates.push(dayjs(date.date).toDate());
				}
				availableDates.value = _dates;
				isLoading.value = false;
			});
	});

const availableSlots = ref<{ id: string, free: string, duration: number, date: string }[]>([]);
const chosenSlot = ref<{ id: string, free: string, duration: number, date: string }>({ id: '', free: '', duration: 0, date: '' });

const chosenDate = ref('');

const chooseDate = async function (date: { id: string }) {
	isLoading.value = true;
	chosenDate.value = date.id;
	let response = await fetch('http://localhost:8080/api/v1/appointments/' + appointment.value.id + '/get-available-slots/' + date.id);
	isLoading.value = false;
	availableSlots.value = await response.json();
	chosenSlot.value.id = '';
};


const submitBooking = async function() {
	step.value++;
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

				<n-space>
					<n-button
						v-for="slot in availableSlots"
						:key="slot.id"
						:type="chosenSlot.id == slot.id ? 'success' : 'default'"
						v-on:click="chosenSlot = slot"
					>{{ dayjs(slot.date).format('HH:mm') }} &mdash; {{ dayjs(slot.date).add(slot.duration, 'minute').format('HH:mm') }}</n-button>
				</n-space>

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
						<tr><td>Termin</td><td>{{ dayjs(chosenSlot.date).format('dddd, DD.MM.YYYY') }}</td></tr>
						<tr><td>Zeit</td><td>{{ dayjs(chosenSlot.date).format('HH:mm') }} &mdash; {{ dayjs(chosenSlot.date).add(chosenSlot.duration, 'minute').format('HH:mm') }}</td></tr>
					</tbody>
				</n-table>

				<n-form :model="contact" ref="contactFormRef" :rules="formRules">
					<n-form-item item path="firstname" label="Vorname">
						<n-input v-model:value="contact.firstname" placeholder="Vorname ..." />
					</n-form-item>
					<n-form-item item path="lastname" label="Nachname">
						<n-input v-model:value="contact.lastname" placeholder="Nachname ..." />
					</n-form-item>
					<n-form-item item path="email" label="E-Mail">
						<n-input v-model:value="contact.email" placeholder="E-Mail ..." />
					</n-form-item>

					<n-form-item v-if="appointment.canComment" item path="comment" label="Anmerkung">
						<n-input type="textarea" v-model:value="contact.comment" placeholder="Anmerkung ..." />
					</n-form-item>
				</n-form>

				<n-button
					class="mt-3 mb-1 w-full"
					size="large"
					type="info"
					v-on:click="submitContact"
				>Weiter</n-button>

				<n-a class="mt-2" v-on:click="step--">Zurück</n-a>
			</div>

			<div v-if="step == 3">

				<n-h2>Buchungsübersicht</n-h2>

				<n-table>
					<tbody>
						<tr><td>Vorname</td><td>{{ contact.firstname}}</td></tr>
						<tr><td>Nachname</td><td>{{ contact.lastname}}</td></tr>
						<tr><td>E-Mail</td><td>{{ contact.email}}</td></tr>
						<tr><td>Termin</td><td>{{ dayjs(chosenSlot.date).format('dddd, DD.MM.YYYY') }}</td></tr>
						<tr><td>Zeit</td><td>{{ dayjs(chosenSlot.date).format('HH:mm') }} &mdash; {{ dayjs(chosenSlot.date).add(chosenSlot.duration, 'minute').format('HH:mm') }}</td></tr>
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

			<div v-if="step == 4">
				<n-result
					status="success"
					title="Termin gebucht"
					description="Sie erhalten eine Bestätigung per E-Mail."
				></n-result>

				<n-table>
					<tbody>
						<tr><td>Vorname</td><td>{{ contact.firstname}}</td></tr>
						<tr><td>Nachname</td><td>{{ contact.lastname}}</td></tr>
						<tr><td>E-Mail</td><td>{{ contact.email}}</td></tr>
						<tr><td>Termin</td><td>{{ dayjs(chosenSlot.date).format('dddd, DD.MM.YYYY') }}</td></tr>
						<tr><td>Zeit</td><td>{{ dayjs(chosenSlot.date).format('HH:mm') }} &mdash; {{ dayjs(chosenSlot.date).add(chosenSlot.duration, 'minute').format('HH:mm') }}</td></tr>
					</tbody>
				</n-table>
				<div v-if="appointment.locationLat"  class="w-full mt-5 rounded-md" style="height: 200px;">
					<l-map :center="[ appointment.locationLat, appointment.locationLng]" :zoom="13" >
						<l-tile-layer url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"></l-tile-layer>
						<l-control-layers />
						<l-marker :lat-lng="[ appointment.locationLat, appointment.locationLng ]">
							<l-popup>Veranstaltungsort</l-popup>
						</l-marker>
					</l-map>
				</div>
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
