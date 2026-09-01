import { Request } from 'express';
import { IUser, IClinic, IUserClinic } from '../models';

export interface AuthRequest extends Request {
  user?: IUser & {
    id: string;
    email: string;
    clinic_id?: string;
    user_clinic?: any;
    permissions?: string[];
    roles?: string[];
    is_admin?: boolean;
  };
  clinic?: {
    id: string;
    name: string;
  };
  clinic_id?: string; // Current selected clinic ID
  userClinics?: IUserClinic[]; // All clinics user has access to
  currentUserClinic?: IUserClinic; // Current user-clinic relationship
  currentClinic?: IClinic; // Current selected clinic details
} 