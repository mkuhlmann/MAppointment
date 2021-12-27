<script lang="ts" setup>
import { ref, h, watch } from 'vue';
import { NText, NDatePicker, NButton, NH2, NModal, NForm, NFormItem, NInput, NIcon, NSwitch, NSpin, NHr, NInputNumber, NMenu } from 'naive-ui';
import CheckIcon from '@vicons/carbon/CheckmarkFilled';
import { useRoute, useRouter } from 'vue-router';

import { Appointment, Slot } from '@/types';
import { useApi } from '@/shared/composables/api';
import SlotModal from '../components/Slot.vue';

const api = useApi();
const router = useRouter();
const route = useRoute();


const slot = ref<Partial<Slot>>({});
const appointment = ref<Partial<Appointment>>({});

const loadingStatus = ref(0);

api.$fetch(`/api/v1/appointments/${route.params.id}`).then(res => {
	appointment.value = res;
	console.log(res);
});



const onAppointmentBlur = async function () {
	loadingStatus.value = 1;

	await api.$fetch(`/api/v1/appointments/${route.params.id}`, {
		method: 'PUT',
		body: appointment.value
	});

	loadingStatus.value = 0;
};

</script>

<template>
	<div class="flex flex-col">
		<div class="flex items-center">
			<n-h2 class="flex-grow">{{ appointment.name }}</n-h2>
			<div
				class="text-md font-semibold"
				:class="{ 'text-green-500': loadingStatus == 0, 'text-cyan-500': loadingStatus == 1 }"
			>
				<div v-if="loadingStatus == 0">
					<n-icon name="edit">
						<CheckIcon />
					</n-icon>Änderungen gespeichert
				</div>
				<div v-else-if="loadingStatus == 1">
					<n-spin />
				</div>
			</div>
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

			<n-form-item label="Beschreibung">
				<n-input type="textarea" v-model:value="appointment.description" @blur="onAppointmentBlur" />
			</n-form-item>

			<div class="flex gap-5">
				<n-form-item label="Veranstaltungsort">
					<n-input v-model:value="appointment.location" @blur="onAppointmentBlur" />
				</n-form-item>

				<n-form-item label="Längengrad">
					<n-input v-model:value="appointment.longitude" @blur="onAppointmentBlur" />
				</n-form-item>

				<n-form-item label="Breitengrad">
					<n-input v-model:value="appointment.latitude" @blur="onAppointmentBlur" />
				</n-form-item>
			</div>
		</n-form>

		<n-text :italic="true">Änderungen werden automatisch gespeichert.</n-text>
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