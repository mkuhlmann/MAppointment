<script lang="ts" setup>
import { defineComponent, ref, inject, computed } from 'vue';
import { darkTheme, NConfigProvider, NCard, NLayout, NSpace, NSteps, NStep } from 'naive-ui';
import { useDark } from '@vueuse/core';

const isDark = useDark();
const useDarkMode = computed(() => {
	if(isDark.value) {
		return darkTheme;
	}
	return null;
});

const step = ref(1);
</script>

<template>
	<n-config-provider :theme="useDarkMode">
		<n-layout class="n-layout" position="absolute">
			<div class="page-container" v-bind:class="{ 'bg-gray-100': !isDark }">
				<router-view></router-view>
			</div>
		</n-layout>		
	</n-config-provider>
</template>

<style>
.page-container {
	min-height: 100%;
	display: flex;
	justify-content: center;
	align-items: center;	
}

.vc-container.vc-is-dark {
	background-color: rgb(16, 16, 20) !important;
	border-color: rgba(255, 255, 255, 0.09)  !important;
}

@media (max-width: 768px) {
	.booking-container {
		border: none !important;
	}

	.n-layout {
	background-color: rgb(24, 24, 28); 
}
}

.booking-container {
	width: 100vw;
	max-width: 600px;
}

@media (min-width: 768px) {
	.booking-container {
		width: 80vw;
	}
}

@media (min-width: 992px) {
	.booking-container {
		width: 60vw;
	}
}

.n-card-header {
	padding-bottom: 0 !important;
}
</style>
