<script lang="ts" setup>
import { NCard, NButton, NDataTable, DataTableColumns, NH2 } from 'naive-ui';
import { ref } from 'vue';

import { Slot } from '@/types';
import { useApi } from '@/shared/composables/api';

const props = defineProps<{ modelValue: Partial<Slot> }>();
const emits = defineEmits<{ 
	(event: 'close'): void
}>();


const api = useApi();

const bookings = ref([]);
const bookingsColumns = [
	{
		title: 'Vorname',
		key: 'firstName'
	},
	{
		title: 'Nachname',
		key: 'lastName'
	},
	{
		title: 'E-Mail',
		key: 'email'
	},
	{
		title: 'Buchungszeit',
		key: 'createdAt'
	}
] as DataTableColumns;

api.$fetch(`/api/v1/slots/${props.modelValue.id}/bookings`).then((res: any) => {
	bookings.value = res;
});

</script>

<template>
	<n-card title="Slot" size="huge">
		<n-h2>Buchungen</n-h2>

		<n-data-table :columns="bookingsColumns" :data="bookings" :pagination="false" />

		<template #footer>
			<n-button type="primary" class="mr-5">Speichern</n-button>
			<n-button type="tertiary" @click="emits('close');">Abbrechen</n-button>

			<n-button class="float-right" type="error">Löschen</n-button>
		</template>
	</n-card>
</template>