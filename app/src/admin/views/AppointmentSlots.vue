<script lang="ts" setup>
import VueCal from 'vue-cal';
import 'vue-cal/dist/vuecal.css';
import 'vue-cal/dist/drag-and-drop.js';
import { NDatePicker, NInputNumber, NButton, NModal, useMessage } from 'naive-ui';
import SlotModal from '../components/Slot.vue';


import { Appointment, Slot } from '@/types';
import { useApi } from '@/shared/composables/api';
import { ref } from 'vue';
import { useRoute } from 'vue-router';
import dayjs from 'dayjs';

const slots = ref<Slot[]>([]);
const api = useApi();
const message = useMessage();

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
	timeRange: [dayjs().startOf('hour').unix() * 1000, dayjs().endOf('hour').unix() * 1000] as [number, number],
	duration: 30
});

const batchCreateSlotsStatus = ref(0);
const batchCreateSlots = async function () {
	const { timeRange, duration } = batchCreateSlotsConfig.value;

	const timeRangeStart = dayjs.unix(timeRange[0] / 1000);
	const timeRangeEnd = dayjs.unix(timeRange[1] / 1000);

	let currentDate = timeRangeStart;
	let currentDateStart = currentDate;
	let currentDateEnd = currentDate.hour(timeRangeEnd.hour()).minute(timeRangeEnd.minute()).second(timeRangeEnd.second());

	let i = 0;
	while (currentDate.isBefore(timeRangeEnd) && i++ < 1000) {
		batchCreateSlotsStatus.value = i;

		await api.$fetch(`/api/v1/appointments/${route.params.id}/slots`, {
			method: 'POST',
			body: {
				slots: 1,
				start: currentDate.toDate(),
				end: currentDate.add(duration, 'minute').toDate()
			}
		});

		currentDate = currentDate.add(duration, 'minute');

		if (currentDate.isAfter(currentDateEnd)) {
			currentDate = currentDateStart = currentDateStart.add(1, 'day');
			currentDateEnd = currentDate.hour(timeRangeEnd.hour()).minute(timeRangeEnd.minute()).second(timeRangeEnd.second());
		}
	}

	batchCreateSlotsStatus.value = 0;

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

	if (event.title) {
		message.error('Terminslot kann nicht geändert werden, da bereits Buchungen vorliegen.');
		fetchSlots();
		return;
	}

	if (event.id) {
		api.$fetch(`/api/v1/slots/${event.id}`, {
			method: 'PUT',
			body: {
				start: event.start,
				end: event.end
			}
		});

		console.debug(`${event.id} changed ${event.start} - ${event.end}`);
		message.success('Terminslot geändert.');
	}
};

const onEventClick = function ($event: any) {
	modalSlot.value = Object.assign({}, $event);
};


const modalSlot = ref<Partial<Slot> | null>(null);
const modalClose = function () {
	console.log('close');
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
	<div class="flex flex-col items-stretch">
		

		<div class="flex items-center">
			<span>Erstelle</span>
			<n-input-number v-model:value="batchCreateSlotsConfig.duration" class="mx-3" />
			<span>Minuten lange Slots im Zeitraum</span>
			<n-date-picker
				v-model:value="batchCreateSlotsConfig.timeRange"
				type="datetimerange"
				class="mx-3"
			/>
			<n-button type="primary" @click="batchCreateSlots" :loading="batchCreateSlotsStatus > 0">Erstellen</n-button>
			<span v-if="batchCreateSlotsStatus > 0">{{ batchCreateSlotsStatus }} Termine erstellt...</span>
		</div>
		<div
			class="mb-5 mt-2 italic"
		>Daten und Zeit werden seperat betrachtet, d.h. es wird an den jeweiligen Tagen jeweils Terminslots zwischen den Zeiten erstellt und nicht über Nacht.</div>

		<div class="">
			<vue-cal
				:selected-date="new Date()"
				:drag-to-create-threshold="0"
				:snap-to-time="5"
				:editable-events="{ title: false, drag: true, resize: true, delete: true, create: true }"
				:events="slots"
				:time-step="30"
				:drag-to-create-event="true"
				@event-drag-create="onEventCreate"
				@event-change="onEventChange"
				@event-click="onEventClick"
			></vue-cal>
		</div>
	</div>
</template>