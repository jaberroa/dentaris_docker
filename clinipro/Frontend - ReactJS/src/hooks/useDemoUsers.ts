import { useState, useEffect } from 'react';
import { User } from '@/types';
import { apiService } from '@/services/api';
import { Shield, Stethoscope, Users, Calculator, UserCheck } from 'lucide-react';

export interface DemoAccount {
  role: string;
  email: string;
  password: string;
  description: string;
  icon: any;
  color: string;
  firstName?: string;
  lastName?: string;
  userId?: string;
}

export const useDemoUsers = () => {
  const [demoAccounts, setDemoAccounts] = useState<DemoAccount[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  // Role configurations
  const roleConfigs = {
    super_admin: {
      description: "Unrestricted system access - bypasses all checks",
      icon: Shield,
      color: "bg-red-100 text-red-800",
    },
    admin: {
      description: "Full system access & management",
      icon: Shield,
      color: "bg-purple-100 text-purple-800",
    },
    doctor: {
      description: "Patient care & medical records",
      icon: Stethoscope,
      color: "bg-blue-100 text-blue-800",
    },
    nurse: {
      description: "Patient care & inventory",
      icon: UserCheck,
      color: "bg-orange-100 text-orange-800",
    },
    receptionist: {
      description: "Appointment & lead management",
      icon: Users,
      color: "bg-green-100 text-green-800",
    },
    accountant: {
      description: "Financial management & reports",
      icon: Calculator,
      color: "bg-pink-100 text-pink-800",
    },
  };

  const fetchDemoUsers = async () => {
    try {
      setLoading(true);
      setError(null);

      // Fetch users for each role (limit 1 per role for demo)
      const roles = ['super_admin', 'admin', 'doctor', 'nurse', 'receptionist', 'accountant'];
      const demoUsers: DemoAccount[] = [];

      for (const role of roles) {
        try {
          // Use the public demo endpoint instead of authenticated users endpoint
          const apiBase = import.meta.env.VITE_API_BASE_URL || 'http://localhost:3000/api';
          const response = await fetch(`${apiBase}/users/demo?role=${role}&is_active=true&limit=1&page=1`, {
            method: 'GET',
            headers: {
              'Content-Type': 'application/json',
            },
          });

          if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
          }

          const data = await response.json();

          if (data.success && data.data.users.length > 0) {
            const user = data.data.users[0];
            const roleConfig = roleConfigs[role as keyof typeof roleConfigs];

            if (roleConfig) {
              // Format role name for display
              const displayRole = role === 'super_admin' 
                ? 'Super Admin' 
                : role.charAt(0).toUpperCase() + role.slice(1);
                
              demoUsers.push({
                role: displayRole,
                email: user.email,
                password: "password123", // All seeded users have this password
                description: roleConfig.description,
                icon: roleConfig.icon,
                color: roleConfig.color,
                firstName: user.first_name,
                lastName: user.last_name,
                userId: user._id
              });
            }
          }
        } catch (roleError) {
          console.warn(`Failed to fetch ${role} users:`, roleError);
          // Continue with other roles even if one fails
        }
      }

      // Set the demo accounts from API (could be empty if no users found)
      setDemoAccounts(demoUsers);

    } catch (err) {
      console.error('Error fetching demo users:', err);
      setError('Failed to load demo accounts from database');
      
      // Don't fall back to static accounts, leave demoAccounts empty
      setDemoAccounts([]);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchDemoUsers();
  }, []);

  return {
    demoAccounts,
    loading,
    error,
    refetch: fetchDemoUsers
  };
};
