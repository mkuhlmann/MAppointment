<script setup lang="ts">
import { ref, reactive } from 'vue';
import { NCard, NForm, NFormItem, NInput, NButton } from 'naive-ui';
import { useApi } from '../../composables/api';
import { useRouter } from 'vue-router';

const api = useApi();
const router = useRouter();
const credentials = reactive({ username: '', password: '' });

const login = async function () {
	if(await api.login(credentials.username, credentials.password)) {
		router.push('/dashboard');
	}
};

if(api.isSignedIn()) {
	router.push('/dashboard');
}
</script>

<template>
	<div class="page-container">
		<n-card class="auth-card" title="MAppointments &mdash; Anmeldung">
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
				
				<n-button v-on:click="login">Anmelden</n-button>
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
