<script setup lang="ts">
import { useApi } from '@/shared/composables/api';
import { FormRules, NForm, NFormItem, NInput, NH2, NButton, useMessage, FormInst } from 'naive-ui';
import { onMounted, reactive, ref } from 'vue';
import { useRoute } from 'vue-router';
import { User } from '../composables/user';

const user = ref<Partial<User>>({});
const api = useApi();
const message = useMessage();

// we fetch user again here to prepare this sfc to be shared with other components
onMounted(() => {
	api.$fetch('/api/v1/auth/user').then(_user => {
		user.value = _user;
	});
});

const userFormRef = ref<FormInst | null>(null);
const save = async () => {
	userFormRef.value?.validate(function (errors: any) {
		if (!errors) {
			let response = api.$fetch('/api/v1/auth/user', {
				method: 'PUT',
				body: user.value
			}).then(response => {
				if (response.error) {
					message.error(response.error);
				} else {
					message.success('Änderungen wurden gespeichert.');
				}
			});
		} else {
			message.error('Bitte füllen Sie alle Felder aus.');
		}
	});
};

const formRules: FormRules = {
	username: {
		required: true,
	},
	password: {
		required: false,
	},
	passwordConfirmation: {
		required: false,
		validator: (rule, value) => {
			if (value !== user.value.password)
				return new Error('Bestätigung stimmt nicht mit Passwort überein');
		},
		trigger: ['input', 'blur']
	},
	email: {
		required: true,
		validator: function (rule, value) {
			if (!/^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/.test(value)) {
				return new Error('Bitte gültige E-Mail angeben');
			}
			return true;
		},
		trigger: ['input', 'blur']
	}
};
</script>

<template>
	<div class="p-5">
		<n-h2>Konto</n-h2>
		<n-form :model="user" ref="userFormRef" :rules="formRules">
			<n-form-item item path="username" label="Username">
				<n-input v-model:value="user.username" placeholder="Username ..." />
			</n-form-item>

			<n-form-item item path="email" label="E-Mail">
				<n-input v-model:value="user.email" placeholder="E-Mail ..." />
			</n-form-item>

			<n-form-item item path="password" label="Passwort">
				<n-input v-model:value="user.password" type="password" placeholder="Passwort ..." />
			</n-form-item>

			<n-form-item item path="passwordConfirmation" label="Passwort wiederholen">
				<n-input
					v-model:value="user.passwordConfirmation"
					type="password"
					placeholder="Passwort wiederholen ..."
				/>
			</n-form-item>
		</n-form>

		<n-button class="mt-3 mb-1 w-full" size="large" type="info" v-on:click="save">Speichern</n-button>
	</div>
</template>