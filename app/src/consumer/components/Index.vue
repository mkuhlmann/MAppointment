<script setup lang="ts">
import { ref } from 'vue';
import { NResult } from 'naive-ui';

defineProps<{}>()

</script>

<template>
    <n-result status="404" title="404 Not Found" description="Seite nicht geunden." />
</template>

<style scoped>
</style>
