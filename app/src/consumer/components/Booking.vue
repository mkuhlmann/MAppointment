<script setup lang="ts">
import { ref, toRefs } from 'vue'
import { NCard, NSteps, NStep, NForm, NFormItem, NInput, FormRules, NDivider, NButton, NSpace, NSpin } from 'naive-ui';
import { useRouter, useRoute } from 'vue-router';

const route = useRoute();
const router = useRouter();

const dates = ref<{ date: string, free: string }[] >([]);
const step = ref(1);
const isLoading = ref(true);

const formRules : FormRules = {
	firstname: {
		required: true,
		trigger: ['input', 'blur'],
		message: 'Bitte Vornamen angeben.'
	},
	lastname: {
		required: true,
		trigger: ['input', 'blur'],
		message: 'Bitte Nachnamen angeben.'
	},
	email: {
		required: true,
		validator: function(rule, value) {
			if(!/^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/.test(value)) {
				return new Error('Bitte gültige E-Mail angeben');
			}
			return true;
		},
		trigger: ['input', 'blur']
	}
};

const contact = ref({
	firstname: '',
	lastname: '',
	email: ''
});


fetch('http://localhost:8080/api/v1/appointments/' + route.params.id)
	.then(response => {
		if (response.status == 404) {
			router.push('/404');
			return;
		}

		fetch('http://localhost:8080/api/v1/appointments/' + route.params.id + '/get-available-dates')
			.then(response => response.json())
			.then(json => {
				dates.value = json;
				isLoading.value = false;
			})
	});
</script>

<template>
<!--
/*const props = defineProps<{ 
	dates: { date: string, free: string }[] 
}>();

const { dates } = toRefs(props);*/-->
<n-spin :show="isLoading">
	<n-card style="width: 25vw; display: block;" title="Terminbuchung">
		<div v-for="date in dates" :key="date.date">
			{{ date.date }}
		</div>

		<n-form :model="contact" :rules="formRules">
			<n-form-item item path="firstname" label="Vorname">
				<n-input v-model:value="contact.firstname" />
			</n-form-item>
			<n-form-item item path="lastname" label="Nachname">
				<n-input v-model:value="contact.lastname" />
			</n-form-item>
			<n-form-item item path="email" label="E-Mail">
				<n-input v-model:value="contact.email" />
			</n-form-item>
		</n-form>

		<n-space justify="end">
			<n-button type="info">Weiter</n-button>
		</n-space>

		<n-divider />


		<n-steps :current="step">
			<n-step title="Datum & Zeit" description="auswählen" />
			<n-step title="Kontaktdaten" description="angeben" />
			<n-step title="Bestätitung" description="erhalten" />
		</n-steps>
	</n-card>
</n-spin>
</template>

<style scoped>
</style>
