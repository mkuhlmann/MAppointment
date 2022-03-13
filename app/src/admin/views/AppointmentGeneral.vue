<script lang="ts" setup>
import { ref, h, watch } from 'vue';
import { NText, NDatePicker, NButton, NH2, NModal, NForm, NFormItem, NInput, NTable, NSwitch, NSpin, NHr, NInputNumber, useMessage } from 'naive-ui';
import CheckIcon from '@vicons/carbon/CheckmarkFilled';
import { useRoute, useRouter } from 'vue-router';

import { Appointment, Slot } from '@/types';
import { useApi } from '@/shared/composables/api';
import SlotModal from '../components/Slot.vue';

const api = useApi();
const router = useRouter();
const route = useRoute();
const message = useMessage();

const slot = ref<Partial<Slot>>({});
const appointment = ref<Partial<Appointment>>({});


api.$fetch(`/api/v1/appointments/${route.params.id}`).then(res => {
	appointment.value = res;
});

const onAppointmentBlur = async function () {
	await api.$fetch(`/api/v1/appointments/${route.params.id}`, {
		method: 'PUT',
		body: appointment.value
	});

	message.success('Termin aktualisiert.');
};
</script>

<template>
	<div class="flex flex-col">
		<n-text :italic="true">Änderungen werden automatisch gespeichert.</n-text>
		<div class="flex items-center">
			<n-h2 class="flex-grow">{{ appointment.name }}</n-h2>
		</div>

		<n-form :model="appointment">
			<div class="flex gap-5">
				<n-form-item label="Aktiv">
					<n-switch
						v-model:value="appointment.isActive"
						:checked-value="1"
						@update-value="onAppointmentBlur"
					/>
				</n-form-item>

				<n-form-item class="flex-grow" label="Name">
					<n-input v-model:value="appointment.name" @blur="onAppointmentBlur" />
				</n-form-item>
			</div>

			<n-form-item label="Beschreibung (Markdown)">
				<n-input
					type="textarea"
					v-model:value="appointment.description"
					@blur="onAppointmentBlur"
					rows="15"
				/>
			</n-form-item>

			<div class="flex gap-5">
				<n-form-item label="Veranstaltungsort">
					<n-input v-model:value="appointment.location" @blur="onAppointmentBlur" />
				</n-form-item>

				<n-form-item label="Breitengrad (für Karte)">
					<n-input-number v-model:value="appointment.latitude" @blur="onAppointmentBlur" />
				</n-form-item>

				<n-form-item label="Längengrad (für Karte)">
					<n-input-number v-model:value="appointment.longitude" @blur="onAppointmentBlur" />
				</n-form-item>

			</div>

			<div class="flex gap-5">
				<n-form-item label="Erfordere E-Mail Bestätigung">
					<n-switch
						v-model:value="appointment.requireMailValidation"
						:checked-value="1"
						@blur="onAppointmentBlur"
					/>
				</n-form-item>

				<n-form-item label="Erfordere Telefonnummer">
					<n-switch
						v-model:value="appointment.requirePhoneNumber"
						:checked-value="1"
						@blur="onAppointmentBlur"
					/>
				</n-form-item>
			</div>

			<n-h2>E-Mail</n-h2>

			<div class="flex gap-5">
				<n-form-item class="flex-1" label="Absender E-Mail">
					<n-input v-model:value="appointment.mailSender" @blur="onAppointmentBlur" />
				</n-form-item>

				<n-form-item class="flex-1" label="Absender Name">
					<n-input v-model:value="appointment.mailSenderName" @blur="onAppointmentBlur" />
				</n-form-item>
			</div>

			<div class="flex gap-5">
				<div class="flex-grow">
					<n-form-item label="Betreff (benötigt Bestätigung)">
						<n-input v-model:value="appointment.mailSubjectValidate" @blur="onAppointmentBlur" />
					</n-form-item>

					<n-form-item label="E-Mail Inhalt (benötigt Bestätigung)">
						<n-input
							type="textarea"
							v-model:value="appointment.mailBodyValidate"
							@blur="onAppointmentBlur"
							rows="10"
						/>
					</n-form-item>

					<n-form-item label="Betreff (bestätigt)">
						<n-input v-model:value="appointment.mailSubjectConfirmation" @blur="onAppointmentBlur" />
					</n-form-item>

					<n-form-item label="E-Mail Inhalt (bestätigt)">
						<n-input
							type="textarea"
							v-model:value="appointment.mailBodyConfirmation"
							@blur="onAppointmentBlur"
							rows="10"
						/>
					</n-form-item>

					<n-form-item label="Betreff (stoniert)">
						<n-input v-model:value="appointment.mailSubjectCancellation" @blur="onAppointmentBlur" />
					</n-form-item>

					<n-form-item label="E-Mail Inhalt (stoniert)">
						<n-input
							type="textarea"
							v-model:value="appointment.mailBodyCancellation"
							@blur="onAppointmentBlur"
							rows="10"
						/>
					</n-form-item>
				</div>
				<div class="w-1/5">
					
					<p class="mb-2">Mögliche Platzhalter:</p>

					<n-table :bordered="false">
						<thead>
							<tr>
								<th>Platzhalter</th>
								<th>Inhalt</th>
							</tr>
						</thead>
						<tbody>
							<tr><td>{firstName}</td><td>Vorname</td></tr>
							<tr><td>{lastName}</td><td>Nachname</td></tr>
							<tr><td>{email}</td><td>E-Mail</td></tr>
							<tr><td>{phone}</td><td>Telefonnummer</td></tr>
							<tr><td>{comment}</td><td>Kommentar</td></tr>
							<tr><td>{appointmentName}</td><td>Veranstaltungsname</td></tr>
							<tr><td>{appointmentDate}</td><td>Termindatum (Lokalisiert)</td></tr>
							<tr><td>{appointmentTime}</td><td>Terminzeit</td></tr>
							<tr><td>{appointmentLocation}</td><td>Veranstaltungsort</td></tr>
							<tr><td>{confirmUrl}</td><td>Bestätigungs-URL</td></tr>
							<tr><td>{url}</td><td>URL</td></tr>
							<tr><td>{bookingDetail}</td><td>Tabellarische Zusammenfassung</td></tr>
							<tr><td>{mailSenderName}</td><td>E-Mail Absender</td></tr>
						</tbody>
					</n-table>

				</div>
			</div>
		</n-form>
	</div>
</template>

<style>
.vuecal__event:nth-child(odd) {
	background-color: #eee;
	border: 1px solid #aaa;
}
.vuecal__event:nth-child(even) {
	background-color: #ddd;
	border: 1px solid #aaa;
}
</style>