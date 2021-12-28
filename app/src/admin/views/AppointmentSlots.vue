<script lang="ts" setup>
import VueCal from 'vue-cal';
import 'vue-cal/dist/vuecal.css';
import 'vue-cal/dist/drag-and-drop.js';
import { NDatePicker, NInputNumber, NButton, NModal } from 'naive-ui';
import SlotModal from '../components/Slot.vue';


import { Appointment, Slot } from '@/types';
import { useApi } from '@/shared/composables/api';
import { ref } from 'vue';
import { useRoute } from 'vue-router';
import dayjs from 'dayjs';

const slots = ref<Slot[]>([]);
const api = useApi();
const modalSlot = ref<Partial<Slot>>({});

const route = useRoute();

api.$fetch(`/api/v1/appointments/${route.params.id}/slots`).then(res => {
	for(const slot of res) {
		slot.start = dayjs.utc(slot.start).local().toDate();
		slot.end = dayjs.utc(slot.end).local().toDate();
	}
	
	slots.value = res;
});


const batchCreateSlotsConfig = ref({
	timeRange: [dayjs().startOf('day').unix()*1000, dayjs().endOf('day').unix()*1000] as [number, number],
	count: 5
});
const batchCreateSlots = async function() {


};


const onEventCreate = async function ($event: any) {
	console.debug(`Event created ${$event.start} - ${$event.end}`);

	let slot = await api.$fetch(`/api/v1/appointments/${route.params.id}/slots`, {
		method: 'POST',
		body: {
			slots: 1,
			start: $event.start,
			end: $event.end
		}
	});
	slots.value.push(slot);

	slot.start = dayjs.utc(slot.start).local().toDate();
	slot.end = dayjs.utc(slot.end).local().toDate();

	console.debug(`--> ${slot.id} created: ${slot.start} - ${slot.end}`);

	return true;
};

const onEventChange = function ($event: any) {
	const event = $event.event;

	if(event.id) {
		api.$fetch(`/api/v1/slots/${event.id}`, {
			method: 'PUT',
			body: {
				start: event.start,
				end: event.end
			}
		});

		console.debug(`${event.id} changed ${event.start} - ${event.end}`);
	}
};

const onEventClick = function ($event: any) {
	modalSlot.value = $event;
};

</script>


<template>

	<n-modal :show="typeof modalSlot.id != 'undefined'" :mask-closable="true">
		<slot-modal v-model="modalSlot" />
	</n-modal>

	<div class="flex items-center">
		<span>Erstelle</span>
		<n-input-number v-model:value="batchCreateSlotsConfig.count" class="mx-3" />
		<span>Minuten lange Slots im Zeitraum</span>
		<n-date-picker v-model:value="batchCreateSlotsConfig.timeRange" type="datetimerange" class="mx-3" />
		<n-button type="primary" @click="batchCreateSlots">Erstellen</n-button>
	</div>

	<div class="h-screen-md">
		<vue-cal
			:selected-date="new Date()"
			:drag-to-create-threshold="0"
			:snap-to-time="5"
			:editable-events="{ title: false, drag: true, resize: true, delete: true, create: true }"
			:events="slots"
			:time-step="15"
			:drag-to-create-event="true"
			@event-drag-create="onEventCreate"
			@event-change="onEventChange"
			@event-click="onEventClick"
		></vue-cal>
	</div>
</template>