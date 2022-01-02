<script lang="ts" setup>
import { useApi } from '@/shared/composables/api';
import { ref, h } from 'vue';
import { NDataTable, DataTableColumns, NButton, NIcon } from 'naive-ui';
import { useRouter } from 'vue-router';
import AddIcon from '@vicons/carbon/Add';

const api = useApi();
const router = useRouter();

const appointments = ref([]);
const appointmentsLoading = ref(true);
const columns: DataTableColumns = [
	{
		title: 'Name',
		key: 'name'
	},

	{
		title: 'Letzte Änderung',
		key: 'updatedAt'
	},

	{
		title: 'Aktionen',
		key: 'actions',
		render: (row) => {
			return h(NButton, {
				size: 'small', onClick: () => {
					router.push(`/appointments/${row.id}`);
				}
			}, { default: () => 'Bearbeiten' });
		}
	}
];

api.$fetch(`/api/v1/appointments`).then(res => {
	appointments.value = res;
	appointmentsLoading.value = false;
});

const create = function() {
	api.$fetch(`/api/v1/appointments`, {
		method: 'POST',
		body: {
			name: 'Neue Veranstaltung'
		}
	}).then(res => {
		router.push(`/appointments/${res.id}`);
	});
};
</script>

<template>
	<div class="p-5">
		<n-button type="primary" @click="create" class="mb-5">
			<template #icon>
				<n-icon>
					<AddIcon />
				</n-icon>
			</template>
			Neue Veranstaltung
		</n-button>
		<n-data-table :data="appointments" :columns="columns" :loading="appointmentsLoading" />
	</div>
</template>