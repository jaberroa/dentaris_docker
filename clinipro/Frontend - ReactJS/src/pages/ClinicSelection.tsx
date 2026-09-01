import React, { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { Loader2, Building2, MapPin, Phone, Mail, Users, Clock, ChevronRight, Lock, Plus, RefreshCw } from 'lucide-react';
import { useClinic, useClinicSelection } from '@/contexts/ClinicContext';
import { useAuth } from '@/contexts/AuthContext';
import { toast } from 'sonner';
import PublicHeader from '@/components/layout/PublicHeader';
import apiService from '@/services/api';
import AddClinicModal from '@/components/modals/AddClinicModal';


// Clinic interface for forms (based on backend model)
interface Clinic {
  id: string;
  name: string;
  code: string;
  description?: string;
  address: {
    street: string;
    city: string;
    state: string;
    zipCode: string;
    country: string;
  };
  contact: {
    phone: string;
    email: string;
    website?: string;
  };
  settings: {
    timezone: string;
    currency: string;
    language: string;
    working_hours: {
      monday: { start: string; end: string; isWorking: boolean };
      tuesday: { start: string; end: string; isWorking: boolean };
      wednesday: { start: string; end: string; isWorking: boolean };
      thursday: { start: string; end: string; isWorking: boolean };
      friday: { start: string; end: string; isWorking: boolean };
      saturday: { start: string; end: string; isWorking: boolean };
      sunday: { start: string; end: string; isWorking: boolean };
    };
  };
  is_active: boolean;
  createdAt: string;
  updatedAt: string;
}


const ClinicSelection: React.FC = () => {
  const navigate = useNavigate();
  const { user } = useAuth();
  const { selectClinic } = useClinic();
  const { userClinics, loading: userClinicsLoading, requiresSelection, hasClinics } = useClinicSelection();
  const [selectedClinicId, setSelectedClinicId] = useState<string | null>(null);
  const [isSelecting, setIsSelecting] = useState(false);
  const [isAddModalOpen, setIsAddModalOpen] = useState(false);
  const [isRefreshing, setIsRefreshing] = useState(false);

  // Check if user is admin or super admin
  const isAdmin = () => {
    if (!user) return false;
    
    // Debug logging to see the actual data structure
    console.log('Debug - user:', user);
    console.log('Debug - userClinics:', userClinics);
    console.log('Debug - userClinics roles:', userClinics.map(uc => ({ 
      clinic: uc.clinic_id?.name, 
      role: uc.role,
      hasRelationship: uc.hasRelationship 
    })));
    
    // Check if user has admin or super_admin role in any of their clinics
    const hasAdminRole = userClinics.some(uc => {
      const role = uc.role as string;
      return (
        role === 'admin' || 
        role === 'super_admin' ||
        role === 'Admin' ||
        role === 'Super Admin' ||
        role === 'ADMIN' ||
        role === 'SUPER_ADMIN'
      );
    });
    
    // Also check if user has a global admin role
    const userGlobalRole = (user as any)?.role || (user as any)?.user_type || (user as any)?.userType;
    const isGlobalAdmin = userGlobalRole === 'admin' || userGlobalRole === 'super_admin' || 
                          userGlobalRole === 'Admin' || userGlobalRole === 'Super Admin';
    
    console.log('Debug - hasAdminRole:', hasAdminRole);
    console.log('Debug - userGlobalRole:', userGlobalRole);
    console.log('Debug - isGlobalAdmin:', isGlobalAdmin);
    
    return hasAdminRole || isGlobalAdmin;
  };


  // Refresh functionality
  const handleRefresh = () => {
    try {
      setIsRefreshing(true);
      // The useClinicSelection hook handles refreshing user clinics automatically
      // Just show a success message to user
      toast.success('Clinics refreshed successfully');
    } catch (error) {
      console.error('Error refreshing clinics:', error);
      toast.error('Failed to refresh clinics');
    } finally {
      setIsRefreshing(false);
    }
  };

  // Add new clinic functionality (similar to Clinics.tsx)
  const handleAddClinic = async (clinicData: Omit<Clinic, "id" | "createdAt" | "updatedAt">) => {
    try {
      // Clean up contact data - only include website if it has a value
      const contactData = {
        phone: clinicData.contact.phone,
        email: clinicData.contact.email,
        ...(clinicData.contact.website && clinicData.contact.website.trim() && {
          website: clinicData.contact.website.trim()
        })
      };

      const createRequest = {
        name: clinicData.name,
        code: clinicData.code,
        description: clinicData.description,
        address: clinicData.address,
        contact: contactData,
        settings: clinicData.settings,
        is_active: clinicData.is_active,
      };

      await apiService.createClinic(createRequest);
      
      setIsAddModalOpen(false);
      toast.success(`${clinicData.name} has been successfully added.`);
      
      // The useClinicSelection hook will automatically refresh to get the latest data
    } catch (error: any) {
      console.error('Error adding clinic:', error);
      
      // Handle validation errors from backend
      if (error.response?.data?.errors && Array.isArray(error.response.data.errors)) {
        const validationErrors = error.response.data.errors;
        const errorMessages = validationErrors.map((err: any) => `${err.path}: ${err.msg}`).join(', ');
        toast.error(errorMessages);
      } else {
        toast.error(error.response?.data?.message || 'Failed to add clinic. Please try again.');
      }
    }
  };


  useEffect(() => {
    // If user doesn't require selection (already has clinic selected), redirect
    if (!requiresSelection && hasClinics) {
      navigate('/dashboard', { replace: true });
    }
  }, [requiresSelection, hasClinics, navigate]);

  const handleSelectClinic = async (clinicId: string, hasAccess: boolean) => {
    if (!hasAccess) {
      toast.error('You do not have access to this clinic. Contact your administrator.');
      return;
    }

    try {
      setIsSelecting(true);
      setSelectedClinicId(clinicId);

      const success = await selectClinic(clinicId);
      
      if (success) {
        toast.success('Clinic selected successfully');
        navigate('/dashboard', { replace: true });
      } else {
        toast.error('Failed to select clinic');
      }
    } catch (error) {
      console.error('Error selecting clinic:', error);
      toast.error('Failed to select clinic');
    } finally {
      setIsSelecting(false);
      setSelectedClinicId(null);
    }
  };

  const getClinicInitials = (name: string): string => {
    return name
      .split(' ')
      .map(word => word.charAt(0))
      .join('')
      .toUpperCase()
      .slice(0, 2);
  };

  const formatAddress = (clinic: any): string => {
    const addr = clinic.address;
    return `${addr.city}, ${addr.state}`;
  };

  const getRoleBadgeColor = (role: string): string => {
    const colors: { [key: string]: string } = {
      super_admin: 'bg-red-200 dark:bg-red-900/30 text-red-900 dark:text-red-200 border-red-300 dark:border-red-700 font-bold',
      admin: 'bg-red-100 dark:bg-red-900/20 text-red-800 dark:text-red-300 border-red-200 dark:border-red-800',
      doctor: 'bg-blue-100 dark:bg-blue-900/20 text-blue-800 dark:text-blue-300 border-blue-200 dark:border-blue-800',
      nurse: 'bg-green-100 dark:bg-green-900/20 text-green-800 dark:text-green-300 border-green-200 dark:border-green-800',
      receptionist: 'bg-purple-100 dark:bg-purple-900/20 text-purple-800 dark:text-purple-300 border-purple-200 dark:border-purple-800',
      accountant: 'bg-orange-100 dark:bg-orange-900/20 text-orange-800 dark:text-orange-300 border-orange-200 dark:border-orange-800',
      staff: 'bg-muted text-muted-foreground border-border'
    };
    return colors[role] || colors.staff;
  };

  const loading = userClinicsLoading;

  if (loading) {
    return (
      <div className="min-h-screen bg-background">
        <PublicHeader showActions={false} />
        <div className="flex items-center justify-center min-h-[calc(100vh-4rem)]">
        <div className="flex flex-col items-center space-y-4">
          <Loader2 className="h-8 w-8 animate-spin text-primary" />
          <p className="text-muted-foreground">Loading clinics...</p>
        </div>
        </div>
      </div>
    );
  }

  if (!hasClinics) {
    return (
      <div className="min-h-screen bg-background">
        <PublicHeader showActions={false} />
        <div className="flex items-center justify-center min-h-[calc(100vh-4rem)]">
        <Card className="w-full max-w-md">
          <CardHeader className="text-center">
            <Building2 className="h-12 w-12 text-muted-foreground mx-auto mb-4" />
            <CardTitle>No Clinics Available</CardTitle>
            <CardDescription>
              You don't have access to any clinics yet. Please contact your administrator to get access.
            </CardDescription>
          </CardHeader>
          <CardContent>
            <Button 
              variant="outline" 
              className="w-full"
              onClick={() => navigate('/login')}
            >
              Back to Login
            </Button>
          </CardContent>
        </Card>
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-background">
      <PublicHeader showActions={false} />

      {/* Content */}
      <div className="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div className="mb-8">
          <div className="flex flex-col space-y-4 sm:space-y-0 sm:flex-row sm:items-center sm:justify-between">
            <div>
              <h2 className="text-2xl font-bold text-foreground mb-2">Your Clinics</h2>
              <p className="text-muted-foreground">
                You have access to {userClinics.filter(uc => uc.hasRelationship).length} clinic{userClinics.filter(uc => uc.hasRelationship).length !== 1 ? 's' : ''}. 
                Select one to continue.
              </p>
            </div>
            
            {/* Admin Actions - Only show for admin users */}
            {isAdmin() && (
              <div className="flex items-center space-x-3">
                <Button
                  variant="outline"
                  onClick={handleRefresh}
                  disabled={isRefreshing || loading}
                >
                  <RefreshCw className={`h-4 w-4 mr-2 ${isRefreshing ? 'animate-spin' : ''}`} />
                  Refresh
                </Button>
                <Button
                  onClick={() => setIsAddModalOpen(true)}
                  disabled={loading}
                >
                  <Plus className="h-4 w-4 mr-2" />
                  Add Clinic
                </Button>
              </div>
            )}
          </div>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          {userClinics.map((userClinic) => {
            const clinic = userClinic.clinic_id;
            const isCurrentlySelecting = selectedClinicId === clinic._id;
            const hasAccess = userClinic.hasRelationship === true;

            return (
              <Card 
                key={clinic._id} 
                className={`transition-all duration-200 relative overflow-hidden ${
                  hasAccess 
                    ? 'hover:shadow-lg cursor-pointer group' 
                    : 'opacity-50 cursor-not-allowed bg-muted/30'
                }`}
                onClick={() => !isSelecting && handleSelectClinic(clinic._id, hasAccess)}
              >
                <CardHeader className="pb-3">
                  <div className="flex items-start justify-between">
                    <div className="flex items-center space-x-3">
                      <Avatar className={`h-12 w-12 ${!hasAccess ? 'opacity-50' : ''}`}>
                        <AvatarFallback className={`font-semibold ${
                          hasAccess 
                            ? 'bg-primary/10 text-primary' 
                            : 'bg-muted text-muted-foreground'
                        }`}>
                          {getClinicInitials(clinic.name)}
                        </AvatarFallback>
                      </Avatar>
                      <div>
                        <div className="flex items-center gap-2">
                          <CardTitle className={`text-lg transition-colors ${
                            hasAccess 
                              ? 'group-hover:text-primary' 
                              : 'text-muted-foreground'
                          }`}>
                            {clinic.name}
                          </CardTitle>
                          {!hasAccess && <Lock className="h-4 w-4 text-muted-foreground" />}
                        </div>
                        <p className="text-sm text-muted-foreground font-mono">
                          {clinic.code}
                        </p>
                      </div>
                    </div>
                    {hasAccess && userClinic.role && (
                      <Badge className={getRoleBadgeColor(userClinic.role)}>
                        {userClinic.role}
                      </Badge>
                    )}
                    {!hasAccess && (
                      <Badge variant="secondary" className="bg-muted text-muted-foreground">
                        No Access
                      </Badge>
                    )}
                  </div>
                </CardHeader>

                <CardContent className="space-y-4">
                  {clinic.description && (
                    <p className="text-sm text-muted-foreground line-clamp-2">
                      {clinic.description}
                    </p>
                  )}

                  <div className="space-y-2">
                    <div className="flex items-center space-x-2 text-sm text-muted-foreground">
                      <MapPin className="h-4 w-4" />
                      <span>{formatAddress(clinic)}</span>
                    </div>
                    
                    <div className="flex items-center space-x-2 text-sm text-muted-foreground">
                      <Phone className="h-4 w-4" />
                      <span>{clinic.contact.phone}</span>
                    </div>

                    <div className="flex items-center space-x-2 text-sm text-muted-foreground">
                      <Mail className="h-4 w-4" />
                      <span>{clinic.contact.email}</span>
                    </div>

                    {hasAccess && userClinic.joined_at && (
                      <div className="flex items-center space-x-2 text-sm text-muted-foreground">
                        <Clock className="h-4 w-4" />
                        <span>Joined {new Date(userClinic.joined_at).toLocaleDateString()}</span>
                      </div>
                    )}

                    {!hasAccess && (
                      <div className="flex items-center space-x-2 text-sm text-muted-foreground">
                        <Lock className="h-4 w-4" />
                        <span>Contact administrator for access</span>
                      </div>
                    )}
                  </div>

                  <div className="pt-4">
                    <Button 
                      className={`w-full transition-all ${
                        hasAccess 
                          ? 'group-hover:bg-primary group-hover:text-primary-foreground' 
                          : ''
                      }`}
                      variant={
                        !hasAccess 
                          ? "secondary" 
                          : isCurrentlySelecting 
                            ? "default" 
                            : "outline"
                      }
                      disabled={isSelecting || !hasAccess}
                    >
                      {isCurrentlySelecting ? (
                        <>
                          <Loader2 className="h-4 w-4 mr-2 animate-spin" />
                          Selecting...
                        </>
                      ) : !hasAccess ? (
                        <>
                          <Lock className="h-4 w-4 mr-2" />
                          No Access
                        </>
                      ) : (
                        <>
                          Access Clinic
                          <ChevronRight className="h-4 w-4 ml-2" />
                        </>
                      )}
                    </Button>
                  </div>
                </CardContent>

                {/* Hover overlay - only for accessible clinics */}
                {hasAccess && (
                  <div className="absolute inset-0 bg-primary/5 opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none" />
                )}
              </Card>
            );
          })}
        </div>

        {/* Footer */}
        <div className="mt-12 text-center">
          <p className="text-sm text-muted-foreground">
            Need access to another clinic? Contact your administrator.
          </p>
        </div>
      </div>

      {/* Add Clinic Modal - Only rendered for admin users */}
      {isAdmin() && (
        <AddClinicModal 
          isOpen={isAddModalOpen}
          onClose={() => setIsAddModalOpen(false)}
          onSubmit={handleAddClinic}
        />
      )}
    </div>
  );
};

export default ClinicSelection; 