<script lang="ts" setup>import { computed } from 'vue';
import { marked } from 'marked';

const props = defineProps<{
	markdown: string;
}>();

const renderedMarkdown = computed(() => {
	const markdown = props.markdown;
	return marked.parse(markdown);
});


</script>

<template>
	<div v-html="renderedMarkdown"></div>
</template>