<script lang="ts" async setup>
import { NCard, NButton, NForm, NFormItem, NInput, NH2, NSwitch, useMessage } from 'naive-ui';
import { ref, h, onBeforeMount } from 'vue';

import { Booking, Slot, Appointment } from '@/types';
import { useApi } from '@/shared/composables/api';

const message = useMessage();
const api = useApi();

const props = defineProps<{ modelValue: Partial<Booking> }>();
const emits = defineEmits<{
	(event: 'close'): void
}>();

const slot = ref<Partial<Slot>>({});
const appointment = ref<Partial<Appointment>>({});

onBeforeMount(async () => {
	slot.value = await api.$fetch('/api/v1/slots/' + props.modelValue.slotId);
	appointment.value = await api.$fetch('/api/v1/appointments/' + slot.value.appointmentId);
});

const save = async () => {
	await api.$fetch('/api/v1/bookings/' + props.modelValue.id, {
		method: 'PUT',
		body: props.modelValue
	});

	message.success('Buchung aktualisiert.');
	emits('close');
};

const sendCancellationMail = ref(true);
const cancel = () => {
	api.$fetch('/api/v1/bookings/' + props.modelValue.id + '/cancel', {
		method: 'POST',
		body: {
			sendCancellationMail: sendCancellationMail.value
		}
	}).then(() => {
		emits('close');
		message.success('Buchung storniert.');
	}).catch(() => {
		message.error('Buchung konnte nicht storniert werden.');
	});
};

</script>

<template>
	<n-card :title="'Buchung ' + slot.start" size="huge">
		<n-form :model="modelValue" ref="bookingFormRef">
			<n-form-item item path="firstName" label="Vorname">
				<n-input v-model:value="modelValue.firstName" placeholder="Vorname ..." />
			</n-form-item>
			<n-form-item item path="lastName" label="Nachname">
				<n-input v-model:value="modelValue.lastName" placeholder="Nachname ..." />
			</n-form-item>
			<n-form-item item path="email" label="E-Mail">
				<n-input v-model:value="modelValue.email" placeholder="E-Mail ..." />
			</n-form-item>

			<n-form-item v-if="appointment.canComment" item path="comment" label="Anmerkung">
				<n-input type="textarea" v-model:value="modelValue.comment" placeholder="Anmerkung ..." />
			</n-form-item>
		</n-form>

		<template #footer>
			<div class="flex items-center gap-5">
				<n-button type="primary" @click="save()">Speichern</n-button>
				<n-button type="tertiary" @click="emits('close');">Abbrechen</n-button>
				<div class="flex-grow"></div>
				 <label>Storno-Mail senden?</label> <n-switch v-model:value="sendCancellationMail" label="E-Mail senden?" />

				<n-button class="float-right" type="error" @click="cancel">Stornieren</n-button>
			</div>
		</template>
	</n-card>
</template>