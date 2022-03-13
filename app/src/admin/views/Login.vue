<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue';
import { NCard, NForm, NFormItem, NInput, NButton, NCheckbox, NAlert, NSpace } from 'naive-ui';
import { useApi } from '@/shared/composables/api';
import { useRouter } from 'vue-router';
import { fetchUser } from '../composables/user';

const api = useApi();
const router = useRouter();
const credentials = reactive({ username: '', password: '', remember: true });

const loginFailed = ref(false);
const isLoading = ref(false);
const isFirstLogin = ref(false);

const login = async function () {
	isLoading.value = true;
	if(await api.login(credentials.username, credentials.password, credentials.remember)) {
		await fetchUser();
		router.push('/');
	} else {
		loginFailed.value = true;
		isLoading.value = false;
	}
};

onMounted(async () => {

	api.$fetch('/api/v1/auth/is-first-login').then(res => {
		isFirstLogin.value = res;
	});

	if(api.isSignedIn() &&  !(await fetchUser()).error) {
		router.push('/');
	}
});

</script>

<template>
	<div class="page-container">
		<n-card class="auth-card" title="MAppointment &mdash; Anmeldung">
			<n-alert v-if="isFirstLogin" type="info" title="Willkommen bei MAppointment!" class="mb-5">
				<p>
					Bitte legen Sie den ersten Benutzer an.
				</p>
			</n-alert>
			<n-form :model="credentials" v-on:submit="login">
				<n-form-item item path="username" label="Nutzer">
					<n-input v-model:value="credentials.username" placeholder="Nuter / E-Mail" />
				</n-form-item>
				<n-form-item item path="password" label="Passwort">
					<n-input
						type="password"
						show-password-on="click"
						v-model:value="credentials.password"
						placeholder="Passwort"
					/>
				</n-form-item>
				<n-form-item item path="remember" label="Angemeldet bleiben">
					<n-checkbox v-model:checked="credentials.remember" />
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
