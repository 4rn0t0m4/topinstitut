<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Establishment;
use App\Models\Practitioner;
use App\Models\TimeOff;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ClientAgendaTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private Establishment $establishment;

    private Practitioner $practitioner;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::parse('next monday')->startOfDay());

        $this->owner = User::factory()->create();
        $this->establishment = Establishment::factory()->create([
            'subscription_tier' => 'premium',
            'subscription_ends_at' => null,
        ]);
        $this->owner->establishments()->attach($this->establishment->id);

        $this->practitioner = $this->establishment->practitioners()->create([
            'name' => 'Alice', 'is_active' => true,
        ]);
        $this->practitioner->schedules()->create(['day_of_week' => 1, 'start_time' => '09:00', 'end_time' => '18:00']);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function url(string $suffix = ''): string
    {
        return '/espace-client/etablissement/'.$this->establishment->id.'/agenda'.$suffix;
    }

    private function existingAppointment(string $time = '10:00', string $status = 'confirmed'): Appointment
    {
        return Appointment::create([
            'establishment_id' => $this->establishment->id,
            'practitioner_id' => $this->practitioner->id,
            'service_name' => 'Massage',
            'duration_minutes' => 60,
            'customer_name' => 'Jean',
            'customer_email' => 'jean@example.com',
            'starts_at' => now()->setTimeFromTimeString($time),
            'ends_at' => now()->setTimeFromTimeString($time)->addMinutes(60),
            'status' => $status,
        ]);
    }

    public function test_owner_can_view_agenda(): void
    {
        $this->actingAs($this->owner)->get($this->url())->assertOk();
    }

    public function test_owner_can_view_week_view(): void
    {
        $this->actingAs($this->owner)
            ->get($this->url().'?view=week')
            ->assertOk();
    }

    public function test_update_status_to_completed(): void
    {
        $appointment = $this->existingAppointment();

        $this->actingAs($this->owner)
            ->patch($this->url().'/'.$appointment->id.'/statut', ['status' => 'completed'])
            ->assertRedirect();

        $this->assertSame('completed', $appointment->fresh()->status);
    }

    public function test_update_status_rejects_invalid_value(): void
    {
        $appointment = $this->existingAppointment();

        $this->actingAs($this->owner)
            ->patch($this->url().'/'.$appointment->id.'/statut', ['status' => 'autre'])
            ->assertSessionHasErrors('status');
    }

    public function test_scope_bindings_block_status_update_on_foreign_appointment(): void
    {
        $other = Establishment::factory()->create(['subscription_tier' => 'premium']);
        $otherPracti = $other->practitioners()->create(['name' => 'Bob', 'is_active' => true]);
        $foreign = Appointment::create([
            'establishment_id' => $other->id,
            'practitioner_id' => $otherPracti->id,
            'service_name' => 'X',
            'duration_minutes' => 30,
            'customer_name' => 'C',
            'customer_email' => 'c@x.com',
            'starts_at' => now(),
            'ends_at' => now()->addMinutes(30),
            'status' => 'confirmed',
        ]);

        // Tentative de modifier ce RDV via l'établissement de l'owner → 404 (scope binding).
        $this->actingAs($this->owner)
            ->patch($this->url().'/'.$foreign->id.'/statut', ['status' => 'cancelled'])
            ->assertNotFound();

        $this->assertSame('confirmed', $foreign->fresh()->status);
    }

    public function test_store_manual_appointment(): void
    {
        $this->actingAs($this->owner)
            ->post($this->url().'/manuel', [
                'practitioner_id' => $this->practitioner->id,
                'service_name' => 'Soin',
                'duration_minutes' => 30,
                'date' => now()->format('Y-m-d'),
                'time' => '14:00',
                'customer_name' => 'Marie',
                'customer_phone' => '0600000000',
            ])
            ->assertRedirect();

        $this->assertSame(1, Appointment::count());
        $appointment = Appointment::first();
        $this->assertSame('Marie', $appointment->customer_name);
        $this->assertSame('Soin', $appointment->service_name);
    }

    public function test_store_manual_overlap_returns_error(): void
    {
        $this->existingAppointment('10:00');

        $this->actingAs($this->owner)
            ->post($this->url().'/manuel', [
                'practitioner_id' => $this->practitioner->id,
                'service_name' => 'Autre',
                'duration_minutes' => 30,
                'date' => now()->format('Y-m-d'),
                'time' => '10:30',
                'customer_name' => 'Conflit',
            ])
            ->assertSessionHasErrors('time');

        $this->assertSame(1, Appointment::count());
    }

    public function test_update_reschedules_appointment(): void
    {
        $appointment = $this->existingAppointment('10:00');

        $this->actingAs($this->owner)
            ->patch($this->url().'/'.$appointment->id, [
                'practitioner_id' => $this->practitioner->id,
                'service_name' => 'Massage',
                'duration_minutes' => 60,
                'date' => now()->format('Y-m-d'),
                'time' => '15:00',
                'customer_name' => 'Jean',
            ])
            ->assertRedirect();

        $appointment->refresh();
        $this->assertSame('15:00', $appointment->starts_at->format('H:i'));
    }

    public function test_update_rejects_overlap_with_another_appointment(): void
    {
        $a = $this->existingAppointment('10:00');
        $b = $this->existingAppointment('14:00');

        // Tente de déplacer B à 10:30 → chevauche A.
        $this->actingAs($this->owner)
            ->patch($this->url().'/'.$b->id, [
                'practitioner_id' => $this->practitioner->id,
                'service_name' => 'Massage',
                'duration_minutes' => 60,
                'date' => now()->format('Y-m-d'),
                'time' => '10:30',
                'customer_name' => 'Jean',
            ])
            ->assertSessionHasErrors('time');

        $this->assertSame('14:00', $b->fresh()->starts_at->format('H:i'));
    }

    public function test_destroy_removes_appointment(): void
    {
        $appointment = $this->existingAppointment();

        $this->actingAs($this->owner)
            ->delete($this->url().'/'.$appointment->id)
            ->assertRedirect();

        $this->assertNull(Appointment::find($appointment->id));
    }

    public function test_store_time_off_blocks_a_range(): void
    {
        $this->actingAs($this->owner)
            ->post($this->url().'/blocage', [
                'practitioner_id' => $this->practitioner->id,
                'starts_at' => now()->setTime(12, 0)->format('Y-m-d H:i'),
                'ends_at' => now()->setTime(14, 0)->format('Y-m-d H:i'),
                'reason' => 'Déjeuner',
            ])
            ->assertRedirect();

        $this->assertSame(1, TimeOff::count());
    }

    public function test_destroy_time_off_removes_it(): void
    {
        $timeOff = $this->practitioner->timeOffs()->create([
            'starts_at' => now()->setTime(12, 0),
            'ends_at' => now()->setTime(13, 0),
        ]);

        $this->actingAs($this->owner)
            ->delete($this->url().'/blocage/'.$this->practitioner->id.'/'.$timeOff->id)
            ->assertRedirect();

        $this->assertNull(TimeOff::find($timeOff->id));
    }

    public function test_non_owner_cannot_view_agenda(): void
    {
        $other = User::factory()->create();

        $this->actingAs($other)->get($this->url())->assertForbidden();
    }

    public function test_non_premium_owner_is_redirected_to_subscription(): void
    {
        $this->establishment->update(['subscription_tier' => 'free']);

        $this->actingAs($this->owner)
            ->get($this->url())
            ->assertRedirect('/espace-client/abonnement');
    }
}
