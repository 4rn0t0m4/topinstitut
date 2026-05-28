<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Establishment;
use App\Models\Practitioner;
use App\Models\Service;
use App\Services\AppointmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AppointmentServiceTest extends TestCase
{
    use RefreshDatabase;

    private AppointmentService $appointments;

    private Establishment $establishment;

    private Practitioner $practitioner;

    private Service $service;

    private Carbon $monday;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::parse('next monday')->startOfDay());

        $this->appointments = app(AppointmentService::class);
        $this->establishment = Establishment::factory()->create();
        $this->practitioner = $this->establishment->practitioners()->create([
            'name' => 'Alice', 'is_active' => true,
        ]);
        $this->service = $this->establishment->services()->create([
            'name' => 'Massage', 'duration_minutes' => 60, 'is_bookable' => true,
        ]);
        $this->practitioner->schedules()->create([
            'day_of_week' => 1, 'start_time' => '09:00', 'end_time' => '18:00',
        ]);
        $this->monday = now()->copy();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function customer(array $overrides = []): array
    {
        return array_merge([
            'customer_name' => 'Jean Dupont',
            'customer_email' => 'jean@example.com',
            'customer_phone' => null,
            'notes' => null,
        ], $overrides);
    }

    public function test_book_creates_a_confirmed_appointment(): void
    {
        $start = $this->monday->copy()->setTime(10, 0);

        $appointment = $this->appointments->book(
            $this->establishment, $this->service, $start, $this->customer(), null, null
        );

        $this->assertNotNull($appointment);
        $this->assertSame('confirmed', $appointment->status);
        $this->assertSame($this->practitioner->id, $appointment->practitioner_id);
        $this->assertSame(60, $appointment->duration_minutes);
        $this->assertTrue($start->equalTo($appointment->starts_at));
        $this->assertTrue($start->copy()->addMinutes(60)->equalTo($appointment->ends_at));
    }

    public function test_book_returns_null_when_no_practitioner_is_free(): void
    {
        // Premier RDV : OK
        $this->appointments->book(
            $this->establishment, $this->service,
            $this->monday->copy()->setTime(10, 0),
            $this->customer(), null, null
        );

        // Deuxième RDV au même créneau : doit échouer (un seul praticien).
        $second = $this->appointments->book(
            $this->establishment, $this->service,
            $this->monday->copy()->setTime(10, 0),
            $this->customer(['customer_email' => 'autre@example.com']),
            null, null
        );

        $this->assertNull($second);
        $this->assertSame(1, Appointment::count());
    }

    public function test_book_respects_specific_practitioner_id(): void
    {
        $other = $this->establishment->practitioners()->create([
            'name' => 'Bob', 'is_active' => true,
        ]);
        $other->schedules()->create(['day_of_week' => 1, 'start_time' => '09:00', 'end_time' => '18:00']);

        $start = $this->monday->copy()->setTime(11, 0);

        $appointment = $this->appointments->book(
            $this->establishment, $this->service, $start, $this->customer(),
            $other->id, null
        );

        $this->assertNotNull($appointment);
        $this->assertSame($other->id, $appointment->practitioner_id);
    }

    public function test_book_manual_creates_appointment_outside_working_hours(): void
    {
        // 20h = en dehors des horaires (9h-18h), mais l'admin peut forcer
        $appointment = $this->appointments->bookManual(
            $this->establishment, $this->practitioner, $this->service,
            [
                'date' => $this->monday->format('Y-m-d'),
                'time' => '20:00',
                'duration_minutes' => 30,
                'customer_name' => 'Tardif',
            ]
        );

        $this->assertNotNull($appointment);
        $this->assertSame(20, $appointment->starts_at->hour);
    }

    public function test_book_manual_returns_null_on_overlap(): void
    {
        $this->appointments->book(
            $this->establishment, $this->service,
            $this->monday->copy()->setTime(10, 0),
            $this->customer(), null, null
        );

        // Tentative manuelle qui chevauche 10:00-11:00
        $conflict = $this->appointments->bookManual(
            $this->establishment, $this->practitioner, $this->service,
            [
                'date' => $this->monday->format('Y-m-d'),
                'time' => '10:30',
                'duration_minutes' => 30,
                'customer_name' => 'Conflit',
            ]
        );

        $this->assertNull($conflict);
    }

    public function test_cancel_marks_future_appointment_as_cancelled(): void
    {
        Notification::fake();
        $this->establishment->update(['email' => 'institut@example.com']);

        $appointment = $this->appointments->book(
            $this->establishment, $this->service,
            $this->monday->copy()->setTime(10, 0),
            $this->customer(), null, null
        );

        $this->assertTrue($this->appointments->cancel($appointment));
        $this->assertSame('cancelled', $appointment->fresh()->status);
    }

    public function test_cancel_refuses_past_or_already_cancelled_appointments(): void
    {
        $appointment = $this->appointments->book(
            $this->establishment, $this->service,
            $this->monday->copy()->setTime(10, 0),
            $this->customer(), null, null
        );
        $appointment->update(['status' => 'cancelled']);

        $this->assertFalse($this->appointments->cancel($appointment->fresh()));
    }

    public function test_cancel_frees_slot_for_re_booking(): void
    {
        $start = $this->monday->copy()->setTime(10, 0);
        $first = $this->appointments->book(
            $this->establishment, $this->service, $start, $this->customer(), null, null
        );
        $this->appointments->cancel($first);

        // Le créneau doit redevenir disponible (l'active_slot devient NULL grâce au statut cancelled).
        $second = $this->appointments->book(
            $this->establishment, $this->service, $start,
            $this->customer(['customer_email' => 'autre@example.com']),
            null, null
        );

        $this->assertNotNull($second, 'Le créneau libéré doit être re-réservable');
        $this->assertNotSame($first->id, $second->id);
    }
}
