export type Appointment = {
	id: string;
	name: string;
	isActive: boolean;

	description: string;
	canComment: boolean;

	location: string;
	longitude?: number;
	latitude?: number;

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