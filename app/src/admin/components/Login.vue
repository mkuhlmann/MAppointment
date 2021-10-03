<script setup lang="ts">
import { ref, reactive } from 'vue';
import { NCard, NForm, NFormItem, NInput, NButton } from 'naive-ui';


const credentials = reactive({ username: '', password: '' });
</script>

<template>
	<n-card class="auth-card" title="MAppointments &mdash; Anmeldung">
		<n-form :model="credentials">
			<n-form-item item path="firstname" label="Nutzer">
				<n-input v-model:value="credentials.username" placeholder="Nuter / E-Mail" />
			</n-form-item>
			<n-form-item item path="lastname" label="Passwort">
				<n-input type="password" show-password-on="click" v-model:value="credentials.password"  placeholder="Passwort" />
			</n-form-item>
		</n-form>

		<n-button>Anmelden</n-button>
	</n-card>
</template>

<style scoped>
.auth-card {
	width: 100vw;
	max-width: 600px;
}
</style>
