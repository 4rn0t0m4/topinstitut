<?php

namespace Tests\Feature;

use App\Models\Establishment;
use App\Notifications\NewBookingNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class BookingFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_submitting_a_booking_creates_message_and_reminder_and_notifies(): void
    {
        Notification::fake();

        $establishment = Establishment::factory()->create(['email' => 'etab@example.com']);

        $this->post('/rdv/'.$establishment->id, [
            'name' => 'Alice',
            'email' => 'alice@example.com',
            'requested_date' => now()->addDays(3)->format('Y-m-d'),
            'requested_time' => 'matin',
        ])->assertRedirect()->assertSessionHas('success');

        $this->assertDatabaseHas('messages', [
            'establishment_id' => $establishment->id,
            'type' => 'booking',
            'email' => 'alice@example.com',
        ]);
        $this->assertDatabaseHas('review_reminders', ['email' => 'alice@example.com']);
        Notification::assertSentTo($establishment, NewBookingNotification::class);
    }

    public function test_booking_requires_name_email_date_time(): void
    {
        $establishment = Establishment::factory()->create();

        $this->post('/rdv/'.$establishment->id, [])
            ->assertSessionHasErrors(['name', 'email', 'requested_date', 'requested_time']);
    }

    public function test_booking_rejects_past_date(): void
    {
        $establishment = Establishment::factory()->create();

        $this->post('/rdv/'.$establishment->id, [
            'name' => 'Alice',
            'email' => 'alice@example.com',
            'requested_date' => '2020-01-01',
            'requested_time' => 'matin',
        ])->assertSessionHasErrors('requested_date');
    }
}
