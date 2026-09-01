import mongoose, { Document, Schema } from 'mongoose';

export interface IInvoice extends Document {
  clinic_id: mongoose.Types.ObjectId;
  patient_id: mongoose.Types.ObjectId;
  appointment_id?: mongoose.Types.ObjectId;
  invoice_number: string;
  total_amount: number;
  tax_amount: number;
  subtotal: number;
  discount: number;
  status: 'draft' | 'sent' | 'pending' | 'paid' | 'overdue' | 'cancelled' | 'refunded';
  issue_date: Date;
  due_date: Date;
  payment_method?: string;
  services: {
    id: string;
    description: string;
    quantity: number;
    unit_price: number;
    total: number;
    type: 'service' | 'test' | 'medication' | 'procedure';
  }[];
  created_at: Date;
  updated_at: Date;
  paid_at?: Date;
}

const InvoiceSchema: Schema = new Schema({
  clinic_id: {
    type: Schema.Types.ObjectId,
    ref: 'Clinic',
    required: [true, 'Clinic ID is required']
  },
  patient_id: {
    type: Schema.Types.ObjectId,
    ref: 'Patient',
    required: [true, 'Patient ID is required']
  },
  appointment_id: {
    type: Schema.Types.ObjectId,
    ref: 'Appointment'
  },
  invoice_number: {
    type: String,
    unique: true,
    trim: true,
    maxlength: [50, 'Invoice number cannot exceed 50 characters']
  },
  total_amount: {
    type: Number,
    min: [0, 'Total amount cannot be negative'],
    validate: {
      validator: function(value: number) {
        return Number.isFinite(value) && value >= 0;
      },
      message: 'Total amount must be a valid positive number'
    }
  },
  tax_amount: {
    type: Number,
    min: [0, 'Tax amount cannot be negative'],
    default: 0,
    validate: {
      validator: function(value: number) {
        return Number.isFinite(value) && value >= 0;
      },
      message: 'Tax amount must be a valid positive number'
    }
  },
  subtotal: {
    type: Number,
    min: [0, 'Subtotal cannot be negative'],
    validate: {
      validator: function(value: number) {
        return Number.isFinite(value) && value >= 0;
      },
      message: 'Subtotal must be a valid positive number'
    }
  },
  discount: {
    type: Number,
    min: [0, 'Discount cannot be negative'],
    default: 0,
    validate: {
      validator: function(value: number) {
        return Number.isFinite(value) && value >= 0;
      },
      message: 'Discount must be a valid positive number'
    }
  },
  status: {
    type: String,
    enum: ['draft', 'sent', 'pending', 'paid', 'overdue', 'cancelled', 'refunded'],
    required: [true, 'Invoice status is required'],
    default: 'draft'
  },
  issue_date: {
    type: Date,
    required: [true, 'Issue date is required'],
    default: Date.now
  },
  due_date: {
    type: Date,
    required: [true, 'Due date is required'],
    validate: {
      validator: function(value: Date) {
        return value >= new Date(Date.now() - 24 * 60 * 60 * 1000); // Allow today or future dates
      },
      message: 'Due date cannot be more than 1 day in the past'
    }
  },
  payment_method: {
    type: String,
    trim: true,
    maxlength: [100, 'Payment method cannot exceed 100 characters']
  },
  services: [{
    id: {
      type: String
    },
    description: {
      type: String,
      required: [true, 'Service description is required'],
      trim: true,
      maxlength: [500, 'Service description cannot exceed 500 characters']
    },
    quantity: {
      type: Number,
      required: [true, 'Service quantity is required'],
      min: [1, 'Service quantity must be at least 1']
    },
    unit_price: {
      type: Number,
      required: [true, 'Service unit price is required'],
      min: [0, 'Service unit price cannot be negative']
    },
    total: {
      type: Number,
      required: [true, 'Service total is required'],
      min: [0, 'Service total cannot be negative']
    },
    type: {
      type: String,
      enum: ['service', 'test', 'medication', 'procedure'],
      required: [true, 'Service type is required'],
      default: 'service'
    }
  }],
  paid_at: {
    type: Date
  }
}, {
  timestamps: { createdAt: 'created_at', updatedAt: 'updated_at' }
});

// Create clinic-aware indexes for better query performance
InvoiceSchema.index({ clinic_id: 1 });
InvoiceSchema.index({ clinic_id: 1, patient_id: 1, created_at: -1 });
InvoiceSchema.index({ clinic_id: 1, status: 1, due_date: 1 });
InvoiceSchema.index({ clinic_id: 1, created_at: -1 });

// Pre-save middleware to generate invoice number and calculate totals
InvoiceSchema.pre('save', async function(next) {
  // Generate invoice number if not provided
  if (!this.invoice_number) {
    const year = new Date().getFullYear();
    const count = await mongoose.model('Invoice').countDocuments({
      created_at: {
        $gte: new Date(year, 0, 1),
        $lt: new Date(year + 1, 0, 1)
      }
    });
    this.invoice_number = `INV-${year}-${String(count + 1).padStart(4, '0')}`;
  }

  // Generate service IDs if not provided
  if (this.isModified('services')) {
    (this.services as any[]).forEach((service: any, index: number) => {
      if (!service.id) {
        service.id = `SRV-${Date.now()}-${index + 1}`;
      }
    });
  }

  // Calculate totals if services have changed
  if (this.isModified('services') || this.isModified('discount') || this.isModified('tax_amount')) {
    this.subtotal = (this.services as any[]).reduce((sum: number, service: any) => sum + service.total, 0);
    
    // Ensure tax_amount and discount have defaults if not provided
    if (this.tax_amount === undefined) this.tax_amount = 0;
    if (this.discount === undefined) this.discount = 0;
    
    this.total_amount = (this.subtotal as number) + (this.tax_amount as number) - (this.discount as number);
  }

  next();
});

// Virtual for gross amount (subtotal + tax)
InvoiceSchema.virtual('gross_amount').get(function() {
  return (this.subtotal as number) + (this.tax_amount as number);
});

// Virtual to check if invoice is overdue
InvoiceSchema.virtual('is_overdue').get(function() {
  return this.status === 'pending' && new Date() > (this.due_date as Date);
});

// Method to calculate days overdue
InvoiceSchema.methods.daysOverdue = function() {
  if (this.status !== 'pending' || new Date() <= this.due_date) {
    return 0;
  }
  const timeDiff = new Date().getTime() - this.due_date.getTime();
  return Math.ceil(timeDiff / (1000 * 3600 * 24));
};

export default mongoose.model<IInvoice>('Invoice', InvoiceSchema); 