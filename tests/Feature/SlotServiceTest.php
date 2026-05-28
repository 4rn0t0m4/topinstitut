<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Establishment;
use App\Models\Practitioner;
use App\Models\Service;
use App\Models\TimeOff;
use App\Services\SlotService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class SlotServiceTest extends TestCase
{
    use RefreshDatabase;

    private SlotService $slots;

    private Establishment $establishment;

    private Practitioner $practitioner;

    private Service $service;

    private Carbon $monday;

    protected function setUp(): void
    {
        parent::setUp();
        // Lundi prochain à 00:00 (jour fixe pour la prédictibilité ISO weekday = 1).
        Carbon::setTestNow(Carbon::parse('next monday')->startOfDay());

        $this->slots = app(SlotService::class);
        $this->establishment = Establishment::factory()->create();
        $this->practitioner = $this->establishment->practitioners()->create([
            'name' => 'Alice',
            'is_active' => true,
        ]);
        $this->service = $this->establishment->services()->create([
            'name' => 'Manucure',
            'duration_minutes' => 60,
            'is_bookable' => true,
        ]);
        // Praticien : lundi 9h-12h puis 14h-18h.
        $this->practitioner->schedules()->create(['day_of_week' => 1, 'start_time' => '09:00', 'end_time' => '12:00']);
        $this->practitioner->schedules()->create(['day_of_week' => 1, 'start_time' => '14:00', 'end_time' => '18:00']);
        // Day filter wants tomorrow (lundi est setNow ; "today" = lundi → on prend mardi pour éviter le filtre "passé"... mais isToday vérifie now()->isToday() seulement si on est sur today)
        // Pour des slots prévus le LUNDI = today : minStart = now()->hour*60+min = 0 → tous les slots passent. Bien.
        $this->monday = now()->copy();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_empty_schedules_yield_no_slots(): void
    {
        $this->practitioner->schedules()->delete();
        $this->practitioner->refresh();

        $slots = $this->slots->availableSlots($this->establishment, $this->service, $this->monday);

        $this->assertSame([], $slots);
    }

    public function test_generates_slots_within_working_ranges(): void
    {
        $slots = $this->slots->availableSlots($this->establishment, $this->service, $this->monday);

        // Service de 60 min, pas de 15 min, plages 9-12 et 14-18 → starts possibles :
        // 9:00, 9:15, 9:30, ..., 11:00 (dernier qui finit à 12) puis 14:00 → 17:00.
        $this->assertContains('09:00', $slots);
        $this->assertContains('11:00', $slots);
        $this->assertNotContains('11:15', $slots, '11:15 + 60 = 12:15 dépasse la fin du matin');
        $this->assertContains('14:00', $slots);
        $this->assertContains('17:00', $slots);
        $this->assertNotContains('17:15', $slots);
    }

    public function test_existing_appointment_blocks_overlapping_slots(): void
    {
        // RDV existant de 10h à 11h
        Appointment::create([
            'establishment_id' => $this->establishment->id,
            'practitioner_id' => $this->practitioner->id,
            'service_id' => $this->service->id,
            'service_name' => 'X',
            'duration_minutes' => 60,
            'customer_name' => 'Client',
            'customer_email' => 'c@x.com',
            'starts_at' => $this->monday->copy()->setTime(10, 0),
            'ends_at' => $this->monday->copy()->setTime(11, 0),
            'status' => 'confirmed',
        ]);

        $slots = $this->slots->availableSlots($this->establishment, $this->service, $this->monday);

        // Tous les starts entre 09:15 et 10:45 chevauchent le RDV ou ne tiennent pas.
        $this->assertNotContains('09:15', $slots, '09:15+60=10:15 chevauche');
        $this->assertNotContains('09:30', $slots);
        $this->assertNotContains('10:00', $slots);
        $this->assertNotContains('10:45', $slots);
        // 09:00 + 60 = 10:00 = début du RDV, donc OK (pas de chevauchement strict)
        $this->assertContains('09:00', $slots);
        // 11:00 + 60 = 12:00, OK (fin du RDV à 11:00)
        $this->assertContains('11:00', $slots);
    }

    public function test_cancelled_appointment_does_not_block_slots(): void
    {
        Appointment::create([
            'establishment_id' => $this->establishment->id,
            'practitioner_id' => $this->practitioner->id,
            'service_id' => $this->service->id,
            'service_name' => 'X',
            'duration_minutes' => 60,
            'customer_name' => 'C',
            'customer_email' => 'c@x.com',
            'starts_at' => $this->monday->copy()->setTime(10, 0),
            'ends_at' => $this->monday->copy()->setTime(11, 0),
            'status' => 'cancelled',
        ]);

        $slots = $this->slots->availableSlots($this->establishment, $this->service, $this->monday);

        $this->assertContains('10:00', $slots);
    }

    public function test_time_off_blocks_slots(): void
    {
        // Pause de 10h à 11h
        TimeOff::create([
            'practitioner_id' => $this->practitioner->id,
            'starts_at' => $this->monday->copy()->setTime(10, 0),
            'ends_at' => $this->monday->copy()->setTime(11, 0),
            'reason' => 'Pause',
        ]);

        $slots = $this->slots->availableSlots($this->establishment, $this->service, $this->monday);

        $this->assertNotContains('10:00', $slots);
        $this->assertContains('11:00', $slots);
    }

    public function test_falls_back_on_establishment_hours_when_practitioner_has_none(): void
    {
        $this->practitioner->schedules()->delete();
        $this->practitioner->refresh();
        // Horaires de l'établissement : lundi 10h-12h
        $this->establishment->schedules()->create([
            'day_of_week' => 1,
            'open_am' => '10:00',
            'close_am' => '12:00',
            'is_closed' => false,
        ]);
        $this->establishment->refresh();

        $slots = $this->slots->availableSlots($this->establishment, $this->service, $this->monday);

        $this->assertContains('10:00', $slots);
        $this->assertContains('11:00', $slots);
        $this->assertNotContains('12:00', $slots);
    }

    public function test_inactive_practitioner_yields_no_slots(): void
    {
        $this->practitioner->update(['is_active' => false]);

        $slots = $this->slots->availableSlots($this->establishment, $this->service, $this->monday);

        $this->assertSame([], $slots);
    }

    public function test_find_free_practitioner_returns_match(): void
    {
        $start = $this->monday->copy()->setTime(10, 0);

        $p = $this->slots->findFreePractitioner($this->establishment, $this->service, $start);

        $this->assertNotNull($p);
        $this->assertSame($this->practitioner->id, $p->id);
    }

    public function test_find_free_practitioner_returns_null_when_busy(): void
    {
        Appointment::create([
            'establishment_id' => $this->establishment->id,
            'practitioner_id' => $this->practitioner->id,
            'service_id' => $this->service->id,
            'service_name' => 'X',
            'duration_minutes' => 60,
            'customer_name' => 'C',
            'customer_email' => 'c@x.com',
            'starts_at' => $this->monday->copy()->setTime(10, 0),
            'ends_at' => $this->monday->copy()->setTime(11, 0),
            'status' => 'confirmed',
        ]);

        $p = $this->slots->findFreePractitioner(
            $this->establishment,
            $this->service,
            $this->monday->copy()->setTime(10, 0),
        );

        $this->assertNull($p);
    }

    public function test_next_availability_skips_to_next_day_with_slots(): void
    {
        // Saturate Monday completely with one big time-off
        TimeOff::create([
            'practitioner_id' => $this->practitioner->id,
            'starts_at' => $this->monday->copy()->setTime(0, 0),
            'ends_at' => $this->monday->copy()->setTime(23, 59),
        ]);
        // Tuesday is day_of_week 2 — pas d'horaires praticien, pas d'horaires établissement → 0 slot.
        // Mercredi (day 3) : on donne au praticien des horaires.
        $this->practitioner->schedules()->create(['day_of_week' => 3, 'start_time' => '09:00', 'end_time' => '12:00']);
        $this->practitioner->refresh();

        $result = $this->slots->nextAvailability($this->establishment, $this->service);

        $this->assertNotNull($result['date']);
        $this->assertSame(3, $result['date']->isoWeekday(), 'Doit sauter à mercredi');
        $this->assertNotEmpty($result['slots']);
    }

    public function test_next_availability_returns_null_date_when_nothing_in_range(): void
    {
        $this->practitioner->schedules()->delete();
        $this->practitioner->refresh();

        $result = $this->slots->nextAvailability($this->establishment, $this->service);

        $this->assertNull($result['date']);
        $this->assertSame([], $result['slots']);
    }
}
