<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue';
import { NCard, NForm, NFormItem, NInput, NButton, NCheckbox, NAlert, NSpace } from 'naive-ui';
import { useApi } from '@/shared/composables/api';
import { useRouter } from 'vue-router';

const api = useApi();
const router = useRouter();
const credentials = reactive({ username: '', password: '', remember: true });

const loginFailed = ref(false);
const isLoading = ref(false);

const login = async function () {
	isLoading.value = true;
	if(await api.login(credentials.username, credentials.password, credentials.remember)) {
		router.push('/');
	} else {
		loginFailed.value = true;
		isLoading.value = false;
	}
};

onMounted(async () => {
	if(api.isSignedIn() &&  !(await api.getUser()).error) {
		router.push('/dashboard');
	}
});

</script>

<template>
	<div class="page-container">
		<n-card class="auth-card" title="MAppointment &mdash; Anmeldung">
			<n-form :model="credentials"  v-on:submit="login">
				<n-form-item item path="firstname" label="Nutzer">
					<n-input v-model:value="credentials.username" placeholder="Nuter / E-Mail" />
				</n-form-item>
				<n-form-item item path="lastname" label="Passwort">
					<n-input
						type="password"
						show-password-on="click"
						v-model:value="credentials.password"
						placeholder="Passwort"
					/>
				</n-form-item>
				<n-form-item item path="remember" label="Angemeldet bleiben">
					<n-checkbox v-model="credentials.remember" />
				</n-form-item>

				<n-alert v-if="loginFailed" class="mb-8" type="error">
					Anmeldung fehlgeschlagen. Bitte überprüfen Sie Ihre Eingaben.
				</n-alert>
				
				<n-button v-on:click="login" :loading="isLoading" type="primary">Anmelden</n-button>
			</n-form>

		</n-card>
	</div>
</template>

<style scoped>
.page-container {
	height: 100%;
	display: flex;
	justify-content: center;
	align-items: center;
}
.auth-card {
	width: 100vw;
	max-width: 600px;
}
</style>
