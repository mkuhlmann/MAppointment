<script lang="ts" setup>
import { useApi } from '@/shared/composables/api';
import { ref, h } from 'vue';
import { NDataTable, DataTableColumns, NButton, NH2 } from 'naive-ui';
import { useRoute, useRouter } from 'vue-router';
import { Appointment } from '@/types';
import 'vue-cal/dist/vuecal.css';
import 'vue-cal/dist/drag-and-drop.js'
import VueCal from 'vue-cal';

const api = useApi();
const router = useRouter();
const route = useRoute();

const events = ref([]);

const appointment = ref<Partial<Appointment>>({});
api.$fetch(`/api/v1/appointments/${route.params.id}`).then(res => {
	appointment.value = res;
});

api.$fetch(`/api/v1/appointments/${route.params.id}/slots`).then(res => {
	events.value = res;
});
</script>

<template>
	<div class="flex flex-col">
		<div class>
			<NH2>{{ appointment.name }}</NH2>
		</div>

		<div class="h-screen-md">
			<vue-cal
				:drag-to-create-threshold="0"
				:snap-to-time="5"
				:editable-events="{ title: true, drag: true, resize: true, delete: true, create: true }"
				:events="events"
				:time-step="15"
			></vue-cal>
		</div>
	</div>
</template>

<style>
.vuecal__event:nth-child(odd) {background-color: #eee; border: 1px solid #aaa; }
.vuecal__event:nth-child(even) {background-color: #ddd; border: 1px solid #aaa; }
</style>