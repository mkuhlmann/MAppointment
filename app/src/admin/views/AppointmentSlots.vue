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

const route = useRoute();

const fetchSlots = async function () {
	let res = await api.$fetch(`/api/v1/appointments/${route.params.id}/slots`);

	for (const slot of res) {
		slot.start = dayjs.utc(slot.start).local().toDate();
		slot.end = dayjs.utc(slot.end).local().toDate();
	}

	slots.value = res;
}
fetchSlots().then();


const batchCreateSlotsConfig = ref({
	timeRange: [dayjs().startOf('day').unix() * 1000, dayjs().endOf('day').unix() * 1000] as [number, number],
	duration: 5
});
const batchCreateSlots = async function () {
	const { timeRange, duration } = batchCreateSlotsConfig.value;

	const minutesStart = Math.floor(timeRange[0] / 1000 / 60);
	const minutesEnd = Math.floor(timeRange[1] / 1000 / 60);

	const slots = [];
	for (let i = minutesStart; i < minutesEnd; i += duration) {

		await api.$fetch(`/api/v1/appointments/${route.params.id}/slots`, {
			method: 'POST',
			body: {
				slots: 1,
				start: new Date(i * 60 * 1000),
				end: new Date((i + duration) * 60 * 1000)
			}
		});
	}

	await fetchSlots();


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

	if (event.id) {
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
	modalSlot.value = Object.assign({}, $event);
};


const modalSlot = ref<Partial<Slot> | null>(null);
const modalClose = function () {
	modalSlot.value = null;
	fetchSlots().then();

};

</script>


<template>
	<n-modal :show="modalSlot != null" :mask-closable="true">
		<div class="w-1/2">
			<slot-modal v-if="modalSlot != null" v-model="modalSlot" @close="modalClose" />
		</div>
	</n-modal>

	<div class="flex items-center">
		<span>Erstelle</span>
		<n-input-number v-model:value="batchCreateSlotsConfig.count" class="mx-3" />
		<span>Minuten lange Slots im Zeitraum</span>
		<n-date-picker
			v-model:value="batchCreateSlotsConfig.timeRange"
			type="datetimerange"
			class="mx-3"
		/>
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