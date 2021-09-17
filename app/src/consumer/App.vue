<script lang="ts" setup>
import { defineComponent, ref, inject } from 'vue';
import { darkTheme, NConfigProvider, NCard, NLayout, NSpace, NSteps, NStep } from 'naive-ui';

const $isDarkMode = inject<boolean>('$isDarkMode');
const step = ref(1);
</script>

<template>
	<n-config-provider :theme="($isDarkMode) ? darkTheme : null">
		<n-layout position="absolute">
			<div class="page-container">
				<router-view></router-view>
			</div>
		</n-layout>		
	</n-config-provider>
</template>

<style scoped>
.page-container {
	min-height: 100vh;
	display: flex;
	justify-content: center;
	align-items: center;
}
</style>
