<script lang="ts" setup>
import { useApi } from '@/shared/composables/api';
import { NCard, NDataTable, DataTableColumns, NSpin,  } from 'naive-ui';
import { computed, h, ref } from 'vue';
import { LineChart } from 'vue-chart-3';
import { Chart, LineController, CategoryScale, LinearScale, PointElement, LineElement, TimeScale } from 'chart.js';
import 'chartjs-adapter-dayjs-3';
import { RouterLink } from 'vue-router';

Chart.register(LineController, CategoryScale, LinearScale, PointElement, LineElement, TimeScale);

const api = useApi();

const bookings = ref([]);
const bookingsColumns = [
	{
		title: 'Veranstaltung',
		key: 'name',
		render: (row) => {
			return h(RouterLink, {
				to: '/appointments/' + row.appointmentId,
				class: 'link'
			}, { default: () => row.name });
		}
	},
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
		title: 'Buchungszeit (UTC)',
		key: 'createdAt'
	}
] as DataTableColumns;

api.$fetch(`/api/v1/bookings/latest`).then((res: any) => {
	bookings.value = res;
});

const bookingStatsLoading = ref(true);
const bookingStatsRawData = ref([]);
const bookingStatsChartOptions = {
	scales: {
		x:
		{
			type: 'time',
			time: {
				unit: 'day'
			}
		}

	}
};
const bookingStatsChartData = computed(() => ({
	datasets: [
		{
			fill: false,
			borderColor: 'rgb(94, 234, 212)',
			data: bookingStatsRawData.value
		}
	]
}));

api.$fetch(`/api/v1/bookings/stats`).then((res: any) => {
	bookingStatsRawData.value = res;
	bookingStatsLoading.value = false;
});

</script>

<template>
	<div class="p-5">
		<div class="flex gap-5">
			<n-card class="flex-1" title="Letze Buchungen">
				<n-data-table :columns="bookingsColumns" :data="bookings" :pagination="false" />
			</n-card>

			<n-spin class="flex-1" :show="bookingStatsLoading">
				<n-card title="Buchungen (letzte 7 Tage)">
					<line-chart :chart-data="bookingStatsChartData" :options="bookingStatsChartOptions" />
				</n-card>
			</n-spin>
		</div>
	</div>
</template>