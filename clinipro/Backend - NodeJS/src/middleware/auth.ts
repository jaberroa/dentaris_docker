import { Response, NextFunction } from 'express';
import jwt from 'jsonwebtoken';
import { User } from '../models';
import { AuthRequest } from '../types/express';

interface JWTPayload {
  id: string;
  email: string;
  role: string;
  clinic_id?: string; // Optional, added after clinic selection
  clinic_role?: string; // User's role in the selected clinic
}

export const authenticate = async (req: AuthRequest, res: Response, next: NextFunction) => {
  try {
    const token = req.header('Authorization')?.replace('Bearer ', '');

    if (!token) {
      res.status(401).json({
        success: false,
        message: 'Access denied. No token provided.'
      });
      return;
    }

    const decoded = jwt.verify(token, process.env.JWT_SECRET || 'your-secret-key') as JWTPayload;
    const user = await User.findById(decoded.id);

    if (!user) {
      res.status(401).json({
        success: false,
        message: 'Invalid token. User not found.'
      });
      return;
    }

    if (!user.is_active) {
      res.status(401).json({
        success: false,
        message: 'Account is deactivated.'
      });
      return;
    }

    // Add user to request
    req.user = user as any;

    // Add clinic context from JWT if present
    if (decoded.clinic_id) {
      req.clinic_id = decoded.clinic_id;
      // Note: Full clinic context validation will be done by clinicContext middleware if needed
    }

    next();
  } catch (error) {
    res.status(401).json({
      success: false,
      message: 'Invalid token.'
    });
  }
};

export const authorize = (...roles: string[]) => {
  return (req: AuthRequest, res: Response, next: NextFunction) => {
    if (!req.user) {
      res.status(401).json({
        success: false,
        message: 'Access denied. Authentication required.'
      });
      return;
    }

    // Super Admin has unrestricted access - bypass all role checks
    if (req.user.role === 'super_admin') {
      next();
      return;
    }

    if (!roles.includes(req.user.role)) {
      res.status(403).json({
        success: false,
        message: 'Access denied. Insufficient permissions.'
      });
      return;
    }

    next();
  };
};

/**
 * Role-based data filtering implementation
 * 
 * This function applies appropriate filters based on user roles:
 * - Admin: Can see all data (no additional filters)
 * - Doctor: Can only see data assigned to them (appointments/prescriptions they created)
 * - Other roles: Can see all data (configurable based on requirements)
 * 
 * @param req AuthRequest containing user information
 * @param filter Existing filter object to be enhanced
 * @returns Enhanced filter object with role-based restrictions
 */
export const applyRoleBasedFilter = (req: AuthRequest, filter: any = {}) => {
  if (!req.user) {
    return filter;
  }

  // Super Admin and Admin can see all data
  if (req.user.role === 'super_admin' || req.user.role === 'admin') {
    return filter;
  }

  // Doctors can only see their assigned data
  if (req.user.role === 'doctor') {
    filter.doctor_id = req.user._id;
    return filter;
  }

  // Other roles (nurse, receptionist, etc.) can see all data for now
  // You can modify this based on your specific requirements
  return filter;
};

/**
 * Helper function to get role-based query filter
 * 
 * This function generates appropriate filters for different entity types:
 * - For appointments/prescriptions: Doctors can only see their own, Nurses can only see their assigned
 * - For patients: Doctors can only see patients they have appointments/prescriptions with, Nurses can only see patients they're assigned to
 * 
 * @param user User object containing role and ID
 * @param entityType Type of entity being queried
 * @returns Filter object with role-based restrictions
 */
export const getRoleBasedFilter = (user: any, entityType: 'appointment' | 'prescription' | 'patient' | 'odontogram' = 'appointment') => {
  const filter: any = {};

  // Super Admin, Admin, receptionist, and staff can see all data
  if (user.role === 'super_admin' || user.role === 'admin' || user.role === 'receptionist' || user.role === 'staff') {
    return filter;
  }

  // Doctors can only see their assigned data
  if (user.role === 'doctor') {
    if (entityType === 'appointment' || entityType === 'prescription' || entityType === 'odontogram') {
      filter.doctor_id = user._id;
    } else if (entityType === 'patient') {
      // For patients, doctors can only see patients they have appointments/prescriptions with
      // This will be handled in the controller with a more complex query
      filter._requiresDoctorPatientFilter = true;
      filter._doctorId = user._id;
    }
    return filter;
  }

  // Nurses can only see their assigned data
  if (user.role === 'nurse') {
    if (entityType === 'appointment') {
      filter.nurse_id = user._id;
    } else if (entityType === 'patient') {
      // For patients, nurses can only see patients they have appointments with (as assigned nurse)
      // This will be handled in the controller with a more complex query
      filter._requiresNursePatientFilter = true;
      filter._nurseId = user._id;
    } else if (entityType === 'prescription') {
      // Nurses can see prescriptions for patients they're assigned to
      filter._requiresNursePrescriptionFilter = true;
      filter._nurseId = user._id;
    }
    return filter;
  }

  // Other roles can see all data for now
  // You can customize this based on your requirements
  return filter;
};

// Combined middleware functions that include authentication
export const requireAdmin = [authenticate, authorize('super_admin', 'admin')];
export const requireDoctor = [authenticate, authorize('super_admin', 'admin', 'doctor')];
export const requireMedicalStaff = [authenticate, authorize('super_admin', 'admin', 'doctor', 'nurse')];
export const requireStaff = [authenticate, authorize('super_admin', 'admin', 'doctor', 'nurse', 'receptionist', 'staff')];
export const requireAnalyticsAccess = [authenticate, authorize('super_admin', 'admin', 'accountant')];
export const requireAllRoles = [authenticate, authorize('super_admin', 'admin', 'doctor', 'nurse', 'receptionist', 'accountant', 'staff')]; 