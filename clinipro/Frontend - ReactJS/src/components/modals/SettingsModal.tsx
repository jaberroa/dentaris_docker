import React, { useState, useEffect } from "react";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Textarea } from "@/components/ui/textarea";
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from "@/components/ui/dialog";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Switch } from "@/components/ui/switch";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";
import {
  Settings,
  Building,
  Clock,
  DollarSign,
  Save,
} from "lucide-react";
import { toast } from "@/hooks/use-toast";
import { useSettings, useUpdateSettings } from "@/hooks/useSettings";
import { useCurrencies } from "@/hooks/useCurrencies";
import { useAuth } from "@/contexts/AuthContext";
import { Skeleton } from "@/components/ui/skeleton";

interface SettingsModalProps {
  trigger?: React.ReactNode;
}

const SettingsModal: React.FC<SettingsModalProps> = ({ trigger }) => {
  const [open, setOpen] = useState(false);
  const [activeTab, setActiveTab] = useState("clinic");

  // API hooks and context
  const { user } = useAuth();
  const { data: settingsData, isLoading: isLoadingSettings, error: settingsError } = useSettings();
  const { data: currencies, isLoading: isLoadingCurrencies } = useCurrencies();
  const updateSettingsMutation = useUpdateSettings();

  // Local state for form data
  const [clinicSettings, setClinicSettings] = useState({
    name: "",
    address: "",
    phone: "",
    email: "",
    website: "",
    description: "",
    logo: "",
  });

  const [workingHours, setWorkingHours] = useState({
    monday: { isOpen: true, start: "09:00", end: "17:00" },
    tuesday: { isOpen: true, start: "09:00", end: "17:00" },
    wednesday: { isOpen: true, start: "09:00", end: "17:00" },
    thursday: { isOpen: true, start: "09:00", end: "17:00" },
    friday: { isOpen: true, start: "09:00", end: "15:00" },
    saturday: { isOpen: false, start: "09:00", end: "13:00" },
    sunday: { isOpen: false, start: "10:00", end: "14:00" },
  });

  const [financialSettings, setFinancialSettings] = useState({
    currency: user?.baseCurrency || "USD",
    taxRate: 10,
    invoicePrefix: "INV",
    paymentTerms: 30,
    defaultDiscount: 0,
  });

  // Load settings data when component mounts or data changes
  useEffect(() => {
    if (settingsData) {
      setClinicSettings({
        name: settingsData.clinic.name || "",
        address: settingsData.clinic.address || "",
        phone: settingsData.clinic.phone || "",
        email: settingsData.clinic.email || "",
        website: settingsData.clinic.website || "",
        description: settingsData.clinic.description || "",
        logo: settingsData.clinic.logo || "",
      });
      
      // Safely update working hours with fallback
      const newWorkingHours = { ...workingHours };
      Object.keys(newWorkingHours).forEach(day => {
        if (settingsData.workingHours[day]) {
          newWorkingHours[day as keyof typeof newWorkingHours] = settingsData.workingHours[day];
        }
      });
      setWorkingHours(newWorkingHours);
      
      setFinancialSettings({
        ...settingsData.financial,
        // Ensure currency defaults to user's preference if not set in clinic settings
        currency: settingsData.financial.currency || user?.baseCurrency || "USD"
      });
    }
  }, [settingsData, user?.baseCurrency]);

  // Update currency when user changes (in case they don't have settings yet)
  useEffect(() => {
    if (!settingsData && user?.baseCurrency) {
      setFinancialSettings(prev => ({
        ...prev,
        currency: user.baseCurrency
      }));
    }
  }, [user?.baseCurrency, settingsData]);

  const handleClinicChange = (field: string, value: string) => {
    setClinicSettings((prev) => ({ ...prev, [field]: value }));
  };

  const handleWorkingHoursChange = (
    day: string,
    field: string,
    value: string | boolean,
  ) => {
    setWorkingHours((prev) => ({
      ...prev,
      [day]: { ...prev[day as keyof typeof prev], [field]: value },
    }));
  };

  const handleFinancialChange = (field: string, value: string | number) => {
    setFinancialSettings((prev) => ({ ...prev, [field]: value }));
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();

    try {
      await updateSettingsMutation.mutateAsync({
        clinic: clinicSettings,
        workingHours,
        financial: financialSettings,
      });

      setOpen(false);
    } catch (error) {
      // Error handling is done in the mutation
      console.error('Settings update failed:', error);
    }
  };

  const days = [
    "monday",
    "tuesday",
    "wednesday",
    "thursday",
    "friday",
    "saturday",
    "sunday",
  ];

  return (
    <Dialog open={open} onOpenChange={setOpen}>
      <DialogTrigger asChild>
        {trigger || (
          <Button>
            <Settings className="h-4 w-4 mr-2" />
            Settings
          </Button>
        )}
      </DialogTrigger>
      <DialogContent className="w-[95vw] max-w-5xl h-[90vh] max-h-[90vh] p-0 flex flex-col">
        <div className="flex flex-col h-full">
          <DialogHeader className="px-4 sm:px-6 pt-4 sm:pt-6 pb-4 border-b flex-shrink-0">
            <DialogTitle className="flex items-center text-lg sm:text-xl">
              <Settings className="h-4 w-4 sm:h-5 sm:w-5 mr-2 text-gray-600" />
              Clinic Settings
            </DialogTitle>
            <DialogDescription className="text-sm text-muted-foreground">
              Configure your clinic's general settings, working hours, and preferences.
            </DialogDescription>
          </DialogHeader>

          <div className="flex-1 min-h-0 flex flex-col">
            <Tabs value={activeTab} onValueChange={setActiveTab} className="flex-1 flex flex-col min-h-0">
              <div className="px-4 sm:px-6 pt-4 border-b flex-shrink-0">
                <TabsList className="grid w-full grid-cols-3 h-auto p-1">
                  <TabsTrigger value="clinic" className="flex items-center gap-1 text-xs sm:text-sm px-2 py-2">
                    <Building className="h-3 w-3 sm:h-4 sm:w-4" />
                    <span className="hidden sm:inline">Clinic</span>
                  </TabsTrigger>
                  <TabsTrigger value="hours" className="flex items-center gap-1 text-xs sm:text-sm px-2 py-2">
                    <Clock className="h-3 w-3 sm:h-4 sm:w-4" />
                    <span className="hidden sm:inline">Hours</span>
                  </TabsTrigger>
                  <TabsTrigger value="financial" className="flex items-center gap-1 text-xs sm:text-sm px-2 py-2">
                    <DollarSign className="h-3 w-3 sm:h-4 sm:w-4" />
                    <span className="hidden sm:inline">Financial</span>
                  </TabsTrigger>
                </TabsList>
              </div>

              <div className="flex-1 min-h-0 overflow-y-auto px-4 sm:px-6 py-4">
                <form onSubmit={handleSubmit} className="space-y-4 sm:space-y-6">
                  {/* Clinic Information Tab */}
                  <TabsContent value="clinic" className="space-y-4 sm:space-y-6 mt-0">
                    <Card className="border border-border">
                      <CardHeader className="pb-3">
                        <CardTitle className="text-base sm:text-lg flex items-center">
                          <Building className="h-4 w-4 mr-2" />
                          Clinic Information
                        </CardTitle>
                      </CardHeader>
                      <CardContent className="space-y-3 sm:space-y-4">
                        {isLoadingSettings ? (
                          <div className="space-y-3">
                            {Array.from({ length: 6 }).map((_, i) => (
                              <Skeleton key={i} className="h-10 w-full" />
                            ))}
                          </div>
                        ) : (
                          <>
                            <div className="grid grid-cols-1 lg:grid-cols-2 gap-3 sm:gap-4">
                              <div className="space-y-2">
                                <Label htmlFor="clinicName" className="text-sm font-medium">Clinic Name *</Label>
                                <Input
                                  id="clinicName"
                                  value={clinicSettings.name}
                                  onChange={(e) => handleClinicChange("name", e.target.value)}
                                  placeholder="Your Clinic Name"
                                  className="h-9 sm:h-10"
                                />
                              </div>
                              <div className="space-y-2">
                                <Label htmlFor="clinicPhone" className="text-sm font-medium">Phone *</Label>
                                <Input
                                  id="clinicPhone"
                                  value={clinicSettings.phone}
                                  onChange={(e) => handleClinicChange("phone", e.target.value)}
                                  placeholder="+1 (555) 123-4567"
                                  className="h-9 sm:h-10"
                                />
                              </div>
                            </div>

                            <div className="grid grid-cols-1 lg:grid-cols-2 gap-3 sm:gap-4">
                              <div className="space-y-2">
                                <Label htmlFor="clinicEmail" className="text-sm font-medium">Email *</Label>
                                <Input
                                  id="clinicEmail"
                                  type="email"
                                  value={clinicSettings.email}
                                  onChange={(e) => handleClinicChange("email", e.target.value)}
                                  placeholder="info@yourclinic.com"
                                  className="h-9 sm:h-10"
                                />
                              </div>
                              <div className="space-y-2">
                                <Label htmlFor="clinicWebsite" className="text-sm font-medium">Website</Label>
                                <Input
                                  id="clinicWebsite"
                                  value={clinicSettings.website}
                                  onChange={(e) => handleClinicChange("website", e.target.value)}
                                  placeholder="https://www.yourclinic.com"
                                  className="h-9 sm:h-10"
                                />
                              </div>
                            </div>

                            <div className="space-y-2">
                              <Label htmlFor="clinicAddress" className="text-sm font-medium">Address *</Label>
                              <Textarea
                                id="clinicAddress"
                                value={clinicSettings.address}
                                onChange={(e) => handleClinicChange("address", e.target.value)}
                                placeholder="123 Medical Center Drive, Suite 100, City, State 12345"
                                className="min-h-[80px] resize-none"
                              />
                            </div>

                            <div className="space-y-2">
                              <Label htmlFor="clinicDescription" className="text-sm font-medium">Description</Label>
                              <Textarea
                                id="clinicDescription"
                                value={clinicSettings.description}
                                onChange={(e) => handleClinicChange("description", e.target.value)}
                                placeholder="Brief description of your clinic and services..."
                                className="min-h-[80px] resize-none"
                              />
                            </div>
                          </>
                        )}
                      </CardContent>
                    </Card>
                  </TabsContent>

                  {/* Working Hours Tab */}
                  <TabsContent value="hours" className="space-y-4 sm:space-y-6 mt-0">
                    <Card className="border border-border">
                      <CardHeader className="pb-3">
                        <CardTitle className="text-base sm:text-lg flex items-center">
                          <Clock className="h-4 w-4 mr-2" />
                          Working Hours
                        </CardTitle>
                      </CardHeader>
                      <CardContent className="space-y-3 sm:space-y-4">
                        {isLoadingSettings ? (
                          <div className="space-y-3">
                            {Array.from({ length: 7 }).map((_, i) => (
                              <Skeleton key={i} className="h-16 w-full" />
                            ))}
                          </div>
                        ) : (
                          <div className="space-y-3 sm:space-y-4">
                            {days.map((day) => (
                              <div key={day} className="border rounded-lg p-3 sm:p-4">
                                <div className="flex flex-col sm:flex-row sm:items-center gap-3 sm:gap-4">
                                  <div className="flex items-center justify-between sm:justify-start sm:w-32">
                                    <Label className="text-sm font-medium capitalize">{day}</Label>
                                    <Switch
                                      checked={workingHours[day as keyof typeof workingHours].isOpen}
                                      onCheckedChange={(checked) =>
                                        handleWorkingHoursChange(day, "isOpen", checked)
                                      }
                                    />
                                  </div>
                                  {workingHours[day as keyof typeof workingHours].isOpen && (
                                    <div className="grid grid-cols-2 gap-2 sm:gap-3 flex-1">
                                      <div className="space-y-1">
                                        <Label className="text-xs text-muted-foreground">Start</Label>
                                        <Input
                                          type="time"
                                          value={workingHours[day as keyof typeof workingHours].start}
                                          onChange={(e) =>
                                            handleWorkingHoursChange(day, "start", e.target.value)
                                          }
                                          className="h-8 sm:h-9"
                                        />
                                      </div>
                                      <div className="space-y-1">
                                        <Label className="text-xs text-muted-foreground">End</Label>
                                        <Input
                                          type="time"
                                          value={workingHours[day as keyof typeof workingHours].end}
                                          onChange={(e) =>
                                            handleWorkingHoursChange(day, "end", e.target.value)
                                          }
                                          className="h-8 sm:h-9"
                                        />
                                      </div>
                                    </div>
                                  )}
                                </div>
                              </div>
                            ))}
                          </div>
                        )}
                      </CardContent>
                    </Card>
                  </TabsContent>

                  {/* Financial Settings Tab */}
                  <TabsContent value="financial" className="space-y-4 sm:space-y-6 mt-0">
                    <Card className="border border-border">
                      <CardHeader className="pb-3">
                        <CardTitle className="text-base sm:text-lg flex items-center">
                          <DollarSign className="h-4 w-4 mr-2" />
                          Financial Settings
                        </CardTitle>
                      </CardHeader>
                      <CardContent className="space-y-3 sm:space-y-4">
                        {isLoadingSettings ? (
                          <div className="space-y-3">
                            {Array.from({ length: 5 }).map((_, i) => (
                              <Skeleton key={i} className="h-10 w-full" />
                            ))}
                          </div>
                        ) : (
                          <>
                            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
                              <div className="space-y-2">
                                <Label htmlFor="currency" className="text-sm font-medium">Currency</Label>
                                <Select
                                  value={financialSettings.currency}
                                  onValueChange={(value) => handleFinancialChange("currency", value)}
                                >
                                  <SelectTrigger className="h-9 sm:h-10">
                                    <SelectValue />
                                  </SelectTrigger>
                                  <SelectContent>
                                    {isLoadingCurrencies ? (
                                      <div className="p-2">
                                        <Skeleton className="h-8 w-full" />
                                      </div>
                                    ) : (
                                      currencies?.map((currency) => (
                                        <SelectItem key={currency.code} value={currency.code}>
                                          {currency.code} ({currency.symbol}) - {currency.name}
                                          {user?.baseCurrency === currency.code && (
                                            <span className="ml-2 text-xs text-green-600">• Your Default</span>
                                          )}
                                        </SelectItem>
                                      ))
                                    )}
                                  </SelectContent>
                                </Select>
                                <p className="text-xs text-muted-foreground">
                                  Clinic's default currency. Your personal preference: {user?.baseCurrency || 'USD'}
                                </p>
                              </div>
                              <div className="space-y-2">
                                <Label htmlFor="taxRate" className="text-sm font-medium">Tax Rate (%)</Label>
                                <Input
                                  id="taxRate"
                                  type="number"
                                  min="0"
                                  max="100"
                                  step="0.1"
                                  value={financialSettings.taxRate}
                                  onChange={(e) => handleFinancialChange("taxRate", parseFloat(e.target.value))}
                                  className="h-9 sm:h-10"
                                />
                              </div>
                              <div className="space-y-2">
                                <Label htmlFor="invoicePrefix" className="text-sm font-medium">Invoice Prefix</Label>
                                <Input
                                  id="invoicePrefix"
                                  value={financialSettings.invoicePrefix}
                                  onChange={(e) => handleFinancialChange("invoicePrefix", e.target.value)}
                                  placeholder="INV"
                                  className="h-9 sm:h-10"
                                />
                              </div>
                            </div>

                            <div className="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                              <div className="space-y-2">
                                <Label htmlFor="paymentTerms" className="text-sm font-medium">Payment Terms (days)</Label>
                                <Input
                                  id="paymentTerms"
                                  type="number"
                                  min="0"
                                  value={financialSettings.paymentTerms}
                                  onChange={(e) => handleFinancialChange("paymentTerms", parseInt(e.target.value))}
                                  className="h-9 sm:h-10"
                                />
                              </div>
                              <div className="space-y-2">
                                <Label htmlFor="defaultDiscount" className="text-sm font-medium">Default Discount (%)</Label>
                                <Input
                                  id="defaultDiscount"
                                  type="number"
                                  min="0"
                                  max="100"
                                  step="0.1"
                                  value={financialSettings.defaultDiscount}
                                  onChange={(e) => handleFinancialChange("defaultDiscount", parseFloat(e.target.value))}
                                  className="h-9 sm:h-10"
                                />
                              </div>
                            </div>
                          </>
                        )}
                      </CardContent>
                    </Card>
                  </TabsContent>
                </form>
              </div>
            </Tabs>
          </div>

          {/* Footer with save button */}
          <div className="border-t bg-background px-4 sm:px-6 py-3 sm:py-4 flex-shrink-0">
            <div className="flex flex-col-reverse sm:flex-row justify-end gap-2 sm:gap-3">
              <Button 
                type="button" 
                variant="outline" 
                onClick={() => setOpen(false)}
                disabled={updateSettingsMutation.isPending}
                className="w-full sm:w-auto"
              >
                Cancel
              </Button>
              <Button 
                type="submit" 
                onClick={handleSubmit}
                disabled={updateSettingsMutation.isPending || isLoadingSettings}
                className="w-full sm:w-auto"
              >
                {updateSettingsMutation.isPending ? (
                  <>
                    <div className="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin mr-2" />
                    Saving...
                  </>
                ) : (
                  <>
                    <Save className="h-4 w-4 mr-2" />
                    Save Settings
                  </>
                )}
              </Button>
            </div>
          </div>
        </div>
      </DialogContent>
    </Dialog>
  );
};

export default SettingsModal;
