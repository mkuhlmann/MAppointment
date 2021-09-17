<script setup lang="ts">
import { ref, toRefs, inject } from 'vue'
import { NCard, NSteps, NStep, NForm, NFormItem, NInput, FormRules, NDivider, NButton, NSpace, NSpin, NText, NP, NH2, NResult } from 'naive-ui';
import { Calendar } from 'v-calendar';
import { useRouter, useRoute } from 'vue-router';
import dayjs from 'dayjs';

const route = useRoute();
const router = useRouter();
const $isMobileScreen = inject<boolean>('$isMobileScreen');
const $isDarkMode = inject<boolean>('$isDarkMode');

const step = ref(1);
const isLoading = ref(true);

const contact = ref({
	firstname: '',
	lastname: '',
	email: ''
});
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


const appointment = ref({ id: '', name: '', description: '' });
const availableDates = ref<Date[]>([]);
fetch('http://localhost:8080/api/v1/appointments/' + route.params.id)
	.then(response => {
		if (response.status == 404) {
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

const availableSlots = ref<{ id: string, free: string, duration: string, date: string }[]>([]);
const chosenSlot = ref({ id: '' });
const chosenDate = ref('');
const chooseDate = async function (date: { id: string }) {
	isLoading.value = true;
	chosenDate.value = date.id;
	let response = await fetch('http://localhost:8080/api/v1/appointments/' + appointment.value.id + '/get-available-slots/' + date.id);
	isLoading.value = false;
	availableSlots.value = await response.json();


};


</script>

<template>
	<!--
/*const props = defineProps<{ 
	dates: { date: string, free: string }[] 
}>();

	const { dates } = toRefs(props);*/-->
	<n-spin :show="isLoading">
		<n-card class="booking-container" :title="appointment.name">
			<n-p>{{ appointment.description }}</n-p>

			<div v-if="step == 1">
				<Calendar
					:available-dates="availableDates"
					is-expanded="true"
					:is-dark="$isDarkMode"
					v-on:dayclick="chooseDate"
				/>

				<n-h2>Verfügbare Termine <span v-if="availableSlots.length > 0">am {{ dayjs(chosenDate).format('dddd, DD.MM.YYYY') }}</span></n-h2>

				<n-p v-if="availableSlots.length == 0">Bitte Datum auswählen.</n-p>

				<div>
					<n-button v-for="slot in availableSlots" :key="slot.id">{{ slot.date }}</n-button>
				</div>
			</div>

			<div v-if="step == 2">
				<n-form :model="contact" :rules="formRules">
					<n-form-item item path="firstname" label="Vorname">
						<n-input v-model:value="contact.firstname" />
					</n-form-item>
					<n-form-item item path="lastname" label="Nachname">
						<n-input v-model:value="contact.lastname" />
					</n-form-item>
					<n-form-item item path="email" label="E-Mail">
						<n-input v-model:value="contact.email" />
					</n-form-item>
				</n-form>
			</div>

			<div v-if="step == 4">
				<n-result status="success" title="Termin gebucht" description="Sie erhalten eine Bestätigung per E-Mail.">
			</n-result></div>

			<n-space justify="end">
				<n-button type="info" v-on:click="step++">Weiter</n-button>
			</n-space>

			<n-divider />

			<n-steps :vertical="$isMobileScreen" :current="step">
				<n-step title="Datum & Zeit" description="auswählen" />
				<n-step title="Kontaktdaten" description="angeben" />
				<n-step title="Bestätitung" description="erhalten" />
			</n-steps>

			<n-divider />

			<n-text depth="3">Impressum</n-text> &mdash;
			<n-text depth="3">Datenschutz</n-text> &mdash;
			<n-text depth="3">Realisiert durch MAppointment</n-text>
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
