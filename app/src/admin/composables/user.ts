import { ref } from 'vue';
import { useApi } from '@/shared/composables/api';

export type User = {
	id: string;
	username: string;
	email: string;
	password: string;
	passwordConfirmation: string;
};

const { $fetch } = useApi();
const user = ref<User | null>(null);

export function useUser() {
	return user;
};

export async function fetchUser() {
	let _user = await $fetch('/api/v1/auth/user');
	if (!_user.error) {
		user.value = _user;
	}
	return _user;
}