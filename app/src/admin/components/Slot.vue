<script lang="ts" setup>
import { NCard, NButton, NDataTable, DataTableColumns, NH2, useMessage } from 'naive-ui';
import { ref, h } from 'vue';

import { Slot } from '@/types';
import { useApi } from '@/shared/composables/api';
import BookingsTable from './BookingsTable.vue';

const message = useMessage();

const props = defineProps<{ modelValue: Partial<Slot> }>();
const emits = defineEmits<{ 
	(event: 'close'): void
}>();


const api = useApi();

const bookings = ref([]);

const remove = function() {
	if(bookings.value.length > 0) {
		message.error('Es sind noch Buchungen vorhanden. Bitte löschen Sie diese zuerst.');
		return;
	}
	api.$fetch('/api/v1/slots/' + props.modelValue.id, {
		method: 'DELETE'
	}).then(() => {
		message.success('Der Terminslot wurde erfolgreich gelöscht.');
		emits('close');
	});
}

api.$fetch(`/api/v1/slots/${props.modelValue.id}/bookings`).then((res: any) => {
	bookings.value = res;
});

</script>

<template>
	<n-card :title="'Termin ' + props.modelValue.start" size="huge">
		<n-h2>Buchungen</n-h2>

		<bookings-table :slotId="props.modelValue.id" />

		<template #footer>
			<n-button type="primary" class="mr-5">Speichern</n-button>
			<n-button type="tertiary" @click="emits('close');">Abbrechen</n-button>

			<n-button class="float-right" type="error" :disabled="bookings.length > 0" @click="remove">
				Löschen
			</n-button>
		</template>
	</n-card>
</template>