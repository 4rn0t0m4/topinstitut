<?php

namespace Tests\Unit;

use App\Models\Establishment;
use App\Notifications\NewBookingNotification;
use App\Services\BookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class BookingServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_persists_message_and_schedules_reminder(): void
    {
        Notification::fake();

        $establishment = Establishment::factory()->create(['email' => 'etab@example.com']);

        $data = [
            'name' => 'Alice',
            'email' => 'alice@example.com',
            'phone' => '0611223344',
            'requested_date' => '2030-06-01',
            'requested_time' => 'matin',
            'requested_service' => 'Manucure',
            'content' => 'Première visite',
        ];

        $message = (new BookingService)->create($establishment, $data);

        $this->assertDatabaseHas('messages', [
            'id' => $message->id,
            'establishment_id' => $establishment->id,
            'type' => 'booking',
            'email' => 'alice@example.com',
            'requested_service' => 'Manucure',
        ]);

        $this->assertDatabaseHas('review_reminders', [
            'message_id' => $message->id,
            'email' => 'alice@example.com',
            'sent_at' => null,
        ]);

        Notification::assertSentTo($establishment, NewBookingNotification::class);
    }

    public function test_create_skips_notification_when_establishment_has_no_email(): void
    {
        Notification::fake();

        $establishment = Establishment::factory()->create(['email' => null]);

        (new BookingService)->create($establishment, [
            'name' => 'Alice',
            'email' => 'alice@example.com',
            'requested_date' => '2030-06-01',
            'requested_time' => 'matin',
        ]);

        Notification::assertNothingSent();
    }
}
