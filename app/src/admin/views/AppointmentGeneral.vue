<script lang="ts" setup>
import { ref, h, watch } from 'vue';
import { NText, NDatePicker, NButton, NH2, NModal, NForm, NFormItem, NInput, NIcon, NSwitch, NSpin, NHr, NInputNumber, useMessage } from 'naive-ui';
import CheckIcon from '@vicons/carbon/CheckmarkFilled';
import { useRoute, useRouter } from 'vue-router';

import { Appointment, Slot } from '@/types';
import { useApi } from '@/shared/composables/api';
import SlotModal from '../components/Slot.vue';

const api = useApi();
const router = useRouter();
const route = useRoute();
const message = useMessage();

const slot = ref<Partial<Slot>>({});
const appointment = ref<Partial<Appointment>>({});


api.$fetch(`/api/v1/appointments/${route.params.id}`).then(res => {
	appointment.value = res;
});

const onAppointmentBlur = async function () {
	await api.$fetch(`/api/v1/appointments/${route.params.id}`, {
		method: 'PUT',
		body: appointment.value
	});

	message.success('Termin aktualisiert.');
};
</script>

<template>
	<div class="flex flex-col">
		

		<n-text :italic="true">Änderungen werden automatisch gespeichert.</n-text>
		<div class="flex items-center">
			<n-h2 class="flex-grow">{{ appointment.name }}</n-h2>
		</div>

		<n-form :model="appointment">
			<div class="flex gap-5">
				<n-form-item label="Aktiv">
					<n-switch
						v-model:value="appointment.isActive"
						:checked-value="1"
						@update-value="onAppointmentBlur"
					/>
				</n-form-item>

				<n-form-item class="flex-grow" label="Name">
					<n-input v-model:value="appointment.name" @blur="onAppointmentBlur" />
				</n-form-item>
			</div>

			<n-form-item label="Beschreibung (Markdown)">
				<n-input type="textarea" v-model:value="appointment.description" @blur="onAppointmentBlur" rows="15" />
			</n-form-item>

			<div class="flex gap-5">
				<n-form-item label="Veranstaltungsort">
					<n-input v-model:value="appointment.location" @blur="onAppointmentBlur" />
				</n-form-item>

				<n-form-item label="Längengrad (für Karte)">
					<n-input-number v-model:value="appointment.longitude" @blur="onAppointmentBlur" />
				</n-form-item>

				<n-form-item label="Breitengrad (für Karte)">
					<n-input-number v-model:value="appointment.latitude" @blur="onAppointmentBlur" />
				</n-form-item>
			</div>

			<div class="flex gap-5">
				<n-form-item label="Erfordere E-Mail Bestätigung">
					<n-switch v-model:value="appointment.requireMailValidation" :checked-value="1" @blur="onAppointmentBlur" />
				</n-form-item>

				<n-form-item label="Erfordere Telefonnummer">
					<n-switch v-model:value="appointment.requirePhoneNumber" :checked-value="1" @blur="onAppointmentBlur" />
				</n-form-item>
			</div>

			<n-h2>E-Mail</n-h2>

			<div class="flex gap-5">
				<n-form-item class="flex-1" label="Absender E-Mail">
					<n-input v-model:value="appointment.mailSender" @blur="onAppointmentBlur" />
				</n-form-item>

				<n-form-item class="flex-1" label="Absender Name">
					<n-input v-model:value="appointment.mailSenderName" @blur="onAppointmentBlur" />
				</n-form-item>

				<n-form-item class="w-1/4" label="Betreff (benötigt Bestätigung)">
					<n-input v-model:value="appointment.mailSubjectValidate" @blur="onAppointmentBlur" />
				</n-form-item>

				<n-form-item class="w-1/4" label="Betreff (bestätigt)">
					<n-input v-model:value="appointment.mailSubject" @blur="onAppointmentBlur" />
				</n-form-item>

			</div>

			
			<n-form-item label="E-Mail Inhalt (benötigt Bestätigung)">
				<n-input type="textarea" v-model:value="appointment.mailBody" @blur="onAppointmentBlur" rows="10" />
			</n-form-item>

			<n-form-item label="E-Mail Inhalt (bestätigt)">
				<n-input type="textarea" v-model:value="appointment.mailBodyValidate" @blur="onAppointmentBlur" rows="10" />
			</n-form-item>

			Mögliche Platzhalter:


		</n-form>
	</div>
</template>

<style>
.vuecal__event:nth-child(odd) {
	background-color: #eee;
	border: 1px solid #aaa;
}
.vuecal__event:nth-child(even) {
	background-color: #ddd;
	border: 1px solid #aaa;
}
</style>