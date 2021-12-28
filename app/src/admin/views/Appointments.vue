<script lang="ts" setup>
import { useApi } from '@/shared/composables/api';
import { ref, h } from 'vue';
import { NDataTable, DataTableColumns, NButton } from 'naive-ui';
import { useRouter } from 'vue-router';

const api = useApi();
const router = useRouter();

const appointments = ref([]);
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
});
</script>

<template>
	<div class="p-5">
		<n-data-table :data="appointments" :columns="columns" :loading="appointments.length == 0" />
	</div>
</template>