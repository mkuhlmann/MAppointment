export type Appointment = {
	id: string;
	name: string;
	description: string;

	canComment: boolean;

	location: string;
	locationLng?: number;
	locationLat?: number;

	updatedAt: Date;
	createdAt: Date;
};