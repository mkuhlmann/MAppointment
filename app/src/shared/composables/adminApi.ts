import { useApi } from './api';

const api = useApi();

export async function createSlot(appointmentId: string, start: Date, end: Date, slots: number = 1) {
	let slot = await api.$fetch(`/api/v1/appointments/${appointmentId}/slots`, {
		method: 'POST',
		body: {
			slots: slots,
			start: start,
			end: end
		}
	});

	return slot;
}

export function useAdminApi() {
	return {
		createSlot
	};
}