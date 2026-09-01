import mongoose from 'mongoose';
import { faker } from '@faker-js/faker';
import { Clinic } from '../models/Clinic';

/**
 * Comprehensive clinic seeder with realistic data
 */
export async function seedClinics(): Promise<mongoose.Types.ObjectId[]> {
  console.log('Seeding clinics...');
  
  try {
    // Clear existing clinics
    await Clinic.deleteMany({});
    
    const clinics: any[] = [];
    
    // Define specific clinic data for variety
    const clinicTemplates = [
      {
        name: 'Central Medical Center',
        type: 'general',
        city: 'New York',
        state: 'NY',
        country: 'USA',
        timezone: 'America/New_York',
        currency: 'USD'
      },
      {
        name: 'Westside Health Group',
        type: 'family',
        city: 'Los Angeles',
        state: 'CA',
        country: 'USA',
        timezone: 'America/Los_Angeles',
        currency: 'USD'
      },
      {
        name: 'Elite Specialty Care',
        type: 'specialty',
        city: 'Chicago',
        state: 'IL',
        country: 'USA',
        timezone: 'America/Chicago',
        currency: 'USD'
      },
      {
        name: 'Metro Community Health',
        type: 'community',
        city: 'Houston',
        state: 'TX',
        country: 'USA',
        timezone: 'America/Chicago',
        currency: 'USD'
      },
      {
        name: 'Northshore Medical Plaza',
        type: 'multi-specialty',
        city: 'Miami',
        state: 'FL',
        country: 'USA',
        timezone: 'America/New_York',
        currency: 'USD'
      }
    ];
    
    for (let i = 0; i < 5; i++) {
      const template = clinicTemplates[i];
      
      const clinic = {
        name: template.name,
        code: `CLN${String(i + 1).padStart(3, '0')}`,
        description: faker.lorem.sentences(2),
        address: {
          street: faker.location.streetAddress(),
          city: template.city,
          state: template.state,
          zipCode: faker.location.zipCode(),
          country: template.country
        },
        contact: {
          phone: faker.phone.number().substring(0, 15),
          email: `contact@${template.name.toLowerCase().replace(/\s+/g, '')}.com`,
          website: `https://${template.name.toLowerCase().replace(/\s+/g, '')}.com`
        },
        settings: {
          timezone: template.timezone,
          currency: template.currency,
          language: 'en',
          working_hours: {
            monday: { 
              start: faker.helpers.arrayElement(['07:00', '08:00', '09:00']), 
              end: faker.helpers.arrayElement(['17:00', '18:00', '19:00']), 
              isWorking: true 
            },
            tuesday: { 
              start: faker.helpers.arrayElement(['07:00', '08:00', '09:00']), 
              end: faker.helpers.arrayElement(['17:00', '18:00', '19:00']), 
              isWorking: true 
            },
            wednesday: { 
              start: faker.helpers.arrayElement(['07:00', '08:00', '09:00']), 
              end: faker.helpers.arrayElement(['17:00', '18:00', '19:00']), 
              isWorking: true 
            },
            thursday: { 
              start: faker.helpers.arrayElement(['07:00', '08:00', '09:00']), 
              end: faker.helpers.arrayElement(['17:00', '18:00', '19:00']), 
              isWorking: true 
            },
            friday: { 
              start: faker.helpers.arrayElement(['07:00', '08:00', '09:00']), 
              end: faker.helpers.arrayElement(['16:00', '17:00', '18:00']), 
              isWorking: true 
            },
            saturday: { 
              start: faker.helpers.arrayElement(['08:00', '09:00', '10:00']), 
              end: faker.helpers.arrayElement(['14:00', '15:00', '16:00']), 
              isWorking: faker.datatype.boolean({ probability: 0.7 })
            },
            sunday: { 
              start: '10:00', 
              end: '15:00', 
              isWorking: faker.datatype.boolean({ probability: 0.3 })
            }
          }
        },
        is_active: true
      };
      
      clinics.push(clinic);
    }
    
    const createdClinics = await Clinic.insertMany(clinics);
    console.log(`  Created ${createdClinics.length} clinics`);
    
    // Log clinic details for reference
    createdClinics.forEach((clinic, index) => {
      console.log(`    ${index + 1}. ${clinic.name} (${clinic.code}) - ${clinic._id}`);
    });
    
    return createdClinics.map(clinic => clinic._id);
    
  } catch (error) {
    console.error('Error seeding clinics:', error);
    throw error;
  }
}
