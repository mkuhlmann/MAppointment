<script lang="ts" setup>
import VueCal from 'vue-cal';
import 'vue-cal/dist/vuecal.css';
import 'vue-cal/dist/drag-and-drop.js';

import { Appointment, Slot } from '@/types';
import { useApi } from '@/shared/composables/api';

const slots = ref([]);

api.$fetch(`/api/v1/appointments/${route.params.id}/slots`).then(res => {
	slots.value = res;
});


const onEventCreate = function ($event: any) {
	console.log($event);
};

const onEventChange = function ($event: any) {
	const event = $event.event;

	console.debug(`Event ${event.id} changed: ${event.start} - ${event.end}`);
};

const onEventClick = function ($event: any) {
	slot.value = $event;
};

</script>

<template>

	<div class="h-screen-md">
		<vue-cal
			:selected-date="new Date()"
			:drag-to-create-threshold="0"
			:snap-to-time="5"
			:editable-events="{ title: false, drag: true, resize: true, delete: true, create: true }"
			:events="slots"
			:time-step="15"
			:drag-to-create-event="true"
			@event-create="onEventCreate"
			@event-change="onEventChange"
			@event-click="onEventClick"
		></vue-cal>
	</div>
</template>