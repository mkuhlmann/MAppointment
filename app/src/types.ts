export type Appointment = {
	id: string;
	name: string;
	isActive: boolean;

	description: string;
	canComment: boolean;
	requireMailValidation: boolean;
	requirePhoneNumber: boolean;

	cancellationEnabled: boolean;
	cancellationDeadline: number;

	location: string;
	longitude?: number;
	latitude?: number;

	mailSender?: string;
	mailSenderName?: string;
	
	mailSubjectConfirmation?: string;	
	mailBodyConfirmation?: string;
	mailBodyCancellation?: string;
	mailSubjectCancellation?: string;
	mailBodyValidate?: string;
	mailSubjectValidate?: string;

	updatedAt: Date;
	createdAt: Date;
};

export type Slot = {
	id: string;
	appointmentId: string;
	
	start: Date;
	end: Date;

	updatedAt: Date;
	createdAt: Date;
};

export type Booking = {
	id: string;
	slotId: string;

	firstName: string;
	lastName: string;
	email: string;
	phone: string;
	comment: string;

	updatedAt: Date;
	createdAt: Date;
}