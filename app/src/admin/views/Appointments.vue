<script lang="ts" setup>
import { useApi } from '@/shared/composables/api';
import { ref } from 'vue';

const api = useApi();

const appointments = ref(await api.$fetch(`/api/v1/appointments`));

</script>

<template>
	Termine
</template>