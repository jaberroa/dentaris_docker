<?php

namespace Tests\Feature\Clinics;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\Quote;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Tests\Concerns\InteractsWithClinicalContext;
use Tests\TestCase;

class ClinicOperationalOwnershipContractTest extends TestCase
{
    use InteractsWithClinicalContext;
    use RefreshDatabase;

    public function test_nullable_root_ownership_schema_has_restricting_foreign_keys_and_children_inherit_owner(): void
    {
        foreach (['suppliers', 'products', 'purchases', 'quotes'] as $table) {
            $this->assertTrue(Schema::hasColumn($table, 'clinic_id'), $table);
        }
        $this->assertFalse(Schema::hasColumn('purchase_items', 'clinic_id'));
        $this->assertFalse(Schema::hasColumn('quote_items', 'clinic_id'));

        $user = User::factory()->create();
        $supplierId = DB::table('suppliers')->insertGetId([
            'supplier_code' => 'NULLABLE-'.uniqid(),
            'company_name' => 'Transición nullable',
            'created_by' => $user->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->assertNull(DB::table('suppliers')->where('id', $supplierId)->value('clinic_id'));

        $migration = require database_path('migrations/2026_09_05_000000_add_clinic_ownership_to_procurement_and_quotes_tables.php');
        $migration->down();
        foreach (['suppliers', 'products', 'purchases', 'quotes'] as $table) {
            $this->assertFalse(Schema::hasColumn($table, 'clinic_id'), $table);
        }
        $this->assertSame('Transición nullable', DB::table('suppliers')->where('id', $supplierId)->value('company_name'));

        $migration->up();
        foreach (['suppliers', 'products', 'purchases', 'quotes'] as $table) {
            $this->assertTrue(Schema::hasColumn($table, 'clinic_id'), $table);
        }

        $clinicId = DB::table('clinics')->insertGetId([
            'code' => 'RESTRICT-'.uniqid(),
            'name' => 'Clínica protegida',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('suppliers')->where('id', $supplierId)->update(['clinic_id' => $clinicId]);
        try {
            DB::table('clinics')->where('id', $clinicId)->delete();
            $this->fail('The clinic foreign key must restrict deletion.');
        } catch (QueryException) {
            $this->assertTrue(DB::table('clinics')->where('id', $clinicId)->exists());
        }
    }

    public function test_scopes_and_policies_use_only_the_active_membership_context(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $context = $this->clinicalContextFor($user, [
            'view_inventory', 'manage_inventory',
            'view_suppliers', 'manage_suppliers',
            'view_purchases', 'manage_purchases',
            'view_quotes', 'manage_quotes',
        ]);
        $otherClinicId = DB::table('clinics')->insertGetId([
            'code' => 'POLICY-OTHER',
            'name' => 'Otra clínica',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->bindClinicalContext($context, $user);

        $models = [
            new Product, new Supplier, new Purchase, new Quote,
        ];
        foreach ($models as $model) {
            $local = clone $model;
            $local->forceFill(['clinic_id' => $context->clinicId]);
            $foreign = clone $model;
            $foreign->forceFill(['clinic_id' => $otherClinicId]);
            $this->assertTrue(Gate::forUser($user)->allows('view', $local), $model::class);
            $this->assertFalse(Gate::forUser($user)->allows('view', $foreign), $model::class);
        }

        $localProduct = new Product;
        $localProduct->forceFill([
            'clinic_id' => $context->clinicId,
            'product_code' => 'LOCAL',
            'name' => 'Local',
            'category' => 'materiales',
            'unit_of_measure' => 'piezas',
            'created_by' => $user->id,
        ])->save();
        $foreignProduct = new Product;
        $foreignProduct->forceFill([
            'clinic_id' => $otherClinicId,
            'product_code' => 'FOREIGN',
            'name' => 'Ajeno',
            'category' => 'materiales',
            'unit_of_measure' => 'piezas',
            'created_by' => $user->id,
        ])->save();

        $this->assertSame([$localProduct->id], Product::forClinic($context)->pluck('id')->all());

        DB::table('clinic_memberships')->where('id', $context->membershipId)->update(['suspended_at' => now()]);
        $this->assertFalse(Gate::forUser($user)->allows('view', $localProduct));
    }
}
