<script lang="ts" setup>
import { useApi } from '@/shared/composables/api';
import BookingsTable from '../components/BookingsTable.vue';
import { useRoute } from 'vue-router';

const route = useRoute();
</script>

<template>
	<BookingsTable :appointmentId="(route.params.id as string)" />
</template>