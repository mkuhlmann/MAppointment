
let jwt: string | null = null;
if (localStorage.getItem('jwt')) {
	jwt = localStorage.getItem('jwt');
}

let isSignedIn = function () {
	return jwt != null;
};

const login = async function (username: string, password: string, remember: boolean = false) {
	const response = await $fetch('/api/v1/auth/login', { body: { username, password } });
	if (response.jwt) {
		jwt = response.jwt;
		if (remember) {
			localStorage.setItem('jwt', response.jwt);
		}
		return true;
	}
	return false;
};

const logout = function () {
	jwt = null;
	localStorage.removeItem('jwt');
};

const $fetch = async function (url: RequestInfo, options: ApiRequestInit = {}) {
	if (!options.headers) options.headers = new Headers();
	else if (typeof options.headers == 'object') options.headers = new Headers(options.headers);

	if (typeof options.body === 'object') {
		options.body = JSON.stringify(options.body);
		options.headers.append('Content-Type', 'application/json');
		if (!options.method) {
			options.method = 'POST';
		}
	}

	if (jwt) {
		options.headers.append('Authorization', `Bearer ${jwt}`);
	}

	const response = await fetch(url, options);
	const json = await response.json();
	if (json.error && options.throwError) {
		throw json.error;
	}
	return json;
};

interface ApiRequestInit extends RequestInit {
	body?: any;
	throwError?: boolean;
}

export function useApi() {
	return { login, logout, isSignedIn, $fetch };
};