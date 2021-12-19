let baseUrl = 'http://localhost:8080';

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

const $fetch = async function (url: RequestInfo, options: ApiRequestInit = {}) {
	url = `${baseUrl}${url}`;
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
	if (json.error) {
		// throw json.error;
	}
	return json;
};

interface ApiRequestInit extends RequestInit {
	body?: any;
}

export function useApi() {
	return { login, isSignedIn, $fetch };
};