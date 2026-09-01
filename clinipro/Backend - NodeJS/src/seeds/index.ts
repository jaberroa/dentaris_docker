import mongoose from 'mongoose';
import * as dotenv from 'dotenv';

// Import centralized database connection
import connectDB, { gracefulShutdown } from '../config/database';

// Import seeder functions
import { seedClinics } from './clinicSeeder';
import { seedUsers } from './userSeeder';
import { seedAllData } from './dataSeeder';
import { seedPermissionSystem, rollbackPermissionSystem } from './permissionSystemSeeder';

dotenv.config();

/**
 * Clear all collections in the database
 */
async function clearDatabase(): Promise<void> {
  const collections = mongoose.connection.collections;
  
  console.log(`Clearing ${Object.keys(collections).length} collections...`);
  
  for (const key in collections) {
    const collection = collections[key];
    const count = await collection.countDocuments({});
    if (count > 0) {
      await collection.deleteMany({});
      console.log(`  Cleared ${key}: ${count} documents`);
    }
  }
  
  console.log('Database cleared successfully\n');
}

/**
 * Run comprehensive seeding for multi-clinic setup
 */
async function runComprehensiveSeeding(): Promise<void> {
  try {
    console.log('Starting comprehensive multi-clinic database seeding...\n');
    console.log('='.repeat(60));

    // 1. Clear database first (if requested)
    const shouldClear = process.argv.includes('--clear') || process.argv.includes('-c');
    if (shouldClear) {
      await clearDatabase();
    }

    // 2. Setup Permission System (IMPORTANT: Do this first)
    console.log('PHASE 1: Setting up Permission System');
    console.log('-'.repeat(40));
    const permissionResult = await seedPermissionSystem();
    
    // 3. Create 5 clinics (foundation)
    console.log('\nPHASE 2: Creating Clinics');
    console.log('-'.repeat(30));
    const clinicIds = await seedClinics();
    
    // 4. Create users and user-clinic relationships
    console.log('\nPHASE 3: Creating Users & Relationships');
    console.log('-'.repeat(40));
    await seedUsers(clinicIds);
    
    // 5. Create comprehensive data for all models (10 rows per clinic)
    console.log('\nPHASE 4: Creating Comprehensive Data');
    console.log('-'.repeat(40));
    await seedAllData(clinicIds);

    // 6. Display summary
    console.log('\n' + '='.repeat(60));
    console.log('SEEDING COMPLETED SUCCESSFULLY!');
    console.log('='.repeat(60));
    console.log('\nSEEDING SUMMARY:');
    console.log(`   Permissions: ${permissionResult.permissions.total} created`);
    console.log(`   Roles: ${permissionResult.roles.total} created`);
    console.log(`   Clinics: ${clinicIds.length} created`);
    console.log(`   Users: ~${clinicIds.length * 10 + 2} created (including super admins)`);
    console.log(`   User-Clinic Relations: ~${clinicIds.length * 10 + clinicIds.length * 2} created`);
    console.log(`   Data Records: ~${clinicIds.length * 200} created across all models`);
    console.log(`   Migrated Users: ${permissionResult.migration.migrated} migrated to new permission system`);
    
    console.log('\nMULTI-CLINIC FEATURES:');
    console.log('   ✅ Advanced Permission System');
    console.log('   ✅ Role-based Access Control');
    console.log('   ✅ 5 distinct clinics with unique settings');
    console.log('   ✅ Clinic-specific data isolation');
    console.log('   ✅ Super admins with multi-clinic access');
    console.log('   ✅ Realistic faker-generated data');
    console.log('   ✅ Proper relationships and foreign keys');
    console.log('   ✅ 10 records per model per clinic');
    
    console.log('\nPERMISSION SYSTEM:');
    console.log('   📋 Granular permissions for all modules');
    console.log('   👥 System roles: Admin, Doctor, Nurse, Receptionist, Accountant, Staff');
    console.log('   🔐 Individual permission overrides');
    console.log('   📊 Permission audit trail');
    console.log('   🏥 Clinic-specific custom roles');
    
    console.log('\nTEST CREDENTIALS:');
    console.log('   Email: Any user email from the generated data');
    console.log('   Password: password123');
    
    console.log('\nNEXT STEPS:');
    console.log('   1. Start the backend server: npm run dev');
    console.log('   2. Test permission system functionality');
    console.log('   3. Test multi-clinic functionality');
    console.log('   4. Verify data isolation between clinics');
    console.log('   5. Test user permissions and clinic access');
    
  } catch (error) {
    console.error('\nError in comprehensive seeding:', error);
    throw error;
  }
}

/**
 * Run permission system setup only
 */
async function runPermissionSeeding(): Promise<any> {
  try {
    console.log('Setting up Permission System only...\n');
    const result = await seedPermissionSystem();
    console.log('\n✅ Permission System setup completed successfully!');
    return result;
  } catch (error) {
    console.error('\n❌ Error setting up permission system:', error);
    throw error;
  }
}

/**
 * Rollback permission system
 */
async function runPermissionRollback(): Promise<any> {
  try {
    console.log('Rolling back Permission System...\n');
    const result = await rollbackPermissionSystem();
    console.log('\n✅ Permission System rollback completed!');
    return result;
  } catch (error) {
    console.error('\n❌ Error rolling back permission system:', error);
    throw error;
  }
}

/**
 * Main seeder execution function
 */
async function main(): Promise<void> {
  const startTime = Date.now();
  
  try {
    // Check command line arguments
    const args = process.argv.slice(2);
    const isPermissionsOnly = args.includes('--permissions') || args.includes('-p');
    const isRollback = args.includes('--rollback') || args.includes('-r');
    const showHelp = args.includes('--help') || args.includes('-h');
    
    if (showHelp) {
      console.log('ClinicPro Database Seeding Commands');
      console.log('====================================');
      console.log('');
      console.log('Usage: npm run seed [options]');
      console.log('');
      console.log('Options:');
      console.log('  --help, -h        Show this help message');
      console.log('  --clear, -c       Clear database before seeding');
      console.log('  --permissions, -p Seed permissions and roles only');
      console.log('  --rollback, -r    Rollback permission system (DEV ONLY)');
      console.log('');
      console.log('Examples:');
      console.log('  npm run seed                    # Full seeding with permissions');
      console.log('  npm run seed --clear            # Clear database and full seed');
      console.log('  npm run seed --permissions      # Setup permissions only');
      console.log('  npm run seed --rollback         # Rollback permissions (DEV)');
      console.log('');
      return;
    }
    
    console.log('ClinicPro Multi-Clinic Database Seeding');
    console.log('==========================================');
    console.log('Creating realistic data with Faker.js');
    console.log('Multi-clinic setup with data isolation');
    console.log('Advanced Permission System included');
    console.log('==========================================\n');
    
    // Connect to database
    console.log('Connecting to database...');
    await connectDB();
    console.log('Connected to database successfully\n');
    
    // Execute based on command line arguments
    if (isRollback) {
      await runPermissionRollback();
    } else if (isPermissionsOnly) {
      await runPermissionSeeding();
    } else {
      await runComprehensiveSeeding();
    }
    
    // Calculate execution time
    const executionTime = ((Date.now() - startTime) / 1000).toFixed(2);
    
    console.log('\n' + '='.repeat(60));
    console.log('SEEDING PROCESS COMPLETED!');
    console.log(`Total execution time: ${executionTime}s`);
    console.log('Disconnecting from database...');
    
    // Graceful shutdown
    await gracefulShutdown();
    console.log('Database connection closed gracefully');
    
    process.exit(0);
    
  } catch (error) {
    console.error('\n' + '='.repeat(60));
    console.error('FATAL ERROR DURING SEEDING');
    console.error('='.repeat(60));
    
    if (error instanceof Error) {
      console.error('Error message:', error.message);
      if (error.stack) {
        console.error('Stack trace:', error.stack);
      }
    }
    
    console.error('\nAttempting graceful shutdown...');
    await gracefulShutdown();
    
    process.exit(1);
  }
}

// Export for use in other files
export { 
  seedClinics, 
  seedUsers, 
  seedAllData, 
  clearDatabase, 
  seedPermissionSystem, 
  rollbackPermissionSystem,
  runPermissionSeeding,
  runPermissionRollback 
};

// Execute main function if this file is run directly
if (require.main === module) {
  main();
}
