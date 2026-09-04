<?php

namespace Tests\Feature\Clinics;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\InteractsWithClinicalContext;
use Tests\TestCase;

class ClinicSelectionTest extends TestCase
{
    use InteractsWithClinicalContext;
    use RefreshDatabase;

    public function test_user_with_one_clinic_selects_and_persists_the_validated_context(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $context = $this->clinicalContextFor($user, ['view_patients']);
        $clinicName = DB::table('clinics')->where('id', $context->clinicId)->value('name');

        $this->actingAs($user)
            ->get(route('clinics.select'))
            ->assertOk()
            ->assertSee($clinicName);

        $this->post(route('clinics.context.store'), ['clinic_id' => $context->clinicId])
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('clinic_id', $context->clinicId)
            ->assertSessionMissing('clinic_site_id');

        $this->assertDatabaseHas('activity_log', [
            'causer_id' => $user->id,
            'log_name' => 'security',
            'description' => 'clinic_context.selected',
        ]);

        $this->get(route('patients.index'))
            ->assertOk()
            ->assertSee($clinicName)
            ->assertSee('clinic-context-dropdown', false);
    }

    public function test_user_with_multiple_clinics_can_change_only_between_authorized_memberships(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $first = $this->clinicalContextFor($user, ['view_patients']);
        $second = $this->clinicalContextFor($user, ['view_patients']);

        $this->actingAs($user)
            ->post(route('clinics.context.store'), ['clinic_id' => $first->clinicId])
            ->assertSessionHas('clinic_id', $first->clinicId);

        $this->post(route('clinics.context.store'), ['clinic_id' => $second->clinicId])
            ->assertSessionHas('clinic_id', $second->clinicId);
    }

    public function test_foreign_inactive_suspended_and_not_activated_clinics_are_rejected(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $authorized = $this->clinicalContextFor($user, ['view_patients']);
        $otherUser = User::factory()->create(['is_active' => true]);
        $foreign = $this->clinicalContextFor($otherUser, ['view_patients']);
        $inactive = $this->clinicalContextFor($user, ['view_patients']);
        $suspended = $this->clinicalContextFor($user, ['view_patients'], null, ['suspended_at' => now()]);
        $notActivated = $this->clinicalContextFor($user, ['view_patients'], null, ['activated_at' => null]);

        DB::table('clinics')->where('id', $inactive->clinicId)->update(['is_active' => false]);

        $this->actingAs($user)->withSession(['clinic_id' => $authorized->clinicId]);
        $selector = $this->get(route('clinics.select'))->assertOk();
        $selector->assertSee(DB::table('clinics')->where('id', $authorized->clinicId)->value('name'));

        foreach ([$foreign, $inactive, $suspended, $notActivated] as $forbidden) {
            $selector->assertDontSee(DB::table('clinics')->where('id', $forbidden->clinicId)->value('name'));
        }

        foreach ([$foreign, $inactive, $suspended, $notActivated] as $forbidden) {
            $this->from(route('clinics.select'))
                ->post(route('clinics.context.store'), ['clinic_id' => $forbidden->clinicId])
                ->assertRedirect(route('clinics.select'))
                ->assertSessionHasErrors('clinic_id')
                ->assertSessionHas('clinic_id', $authorized->clinicId);
        }
    }

    public function test_selector_remains_available_without_context_and_clinical_routes_fail_closed(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $context = $this->clinicalContextFor($user, ['view_patients']);

        $this->actingAs($user)
            ->get(route('clinics.select'))
            ->assertOk();

        $this->get(route('patients.index'))->assertForbidden();

        $this->post(route('clinics.context.store'), ['clinic_id' => $context->clinicId])
            ->assertSessionHas('clinic_id', $context->clinicId);

        $this->get(route('patients.index'))->assertOk();
    }

    public function test_invalid_session_context_is_removed_instead_of_being_trusted(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $this->clinicalContextFor($user, ['view_patients']);

        $this->actingAs($user)
            ->withSession(['clinic_id' => 999999, 'clinic_site_id' => 999999])
            ->get(route('clinics.select'))
            ->assertOk()
            ->assertSessionMissing('clinic_id')
            ->assertSessionMissing('clinic_site_id');
    }

    public function test_login_with_a_stale_context_is_sent_to_the_selector(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $this->clinicalContextFor($user, ['view_patients']);

        $this->withSession(['clinic_id' => 999999, 'clinic_site_id' => 999999])
            ->post(route('login'), [
                'email' => $user->email,
                'password' => 'password',
            ])
            ->assertRedirect(route('clinics.select'))
            ->assertSessionMissing('clinic_id')
            ->assertSessionMissing('clinic_site_id');
    }
}
