<?php

namespace Tests\Unit;

use App\Models\Schedule;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

class ScheduleTest extends TestCase
{
    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function schedule(array $attrs): Schedule
    {
        return new Schedule(array_merge(['is_closed' => false], $attrs));
    }

    public function test_closed_day_returns_closed(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-23 10:00'));

        $s = $this->schedule(['is_closed' => true]);

        $this->assertSame('closed', $s->status);
    }

    public function test_returns_open_when_time_is_within_am_slot(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-23 10:00'));

        $s = $this->schedule(['open_am' => '09:00', 'close_am' => '12:00']);

        $this->assertSame('open', $s->status);
    }

    public function test_returns_closing_soon_when_within_30_min_of_close(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-23 11:45'));

        $s = $this->schedule(['open_am' => '09:00', 'close_am' => '12:00']);

        $this->assertSame('closing_soon', $s->status);
    }

    public function test_returns_opening_soon_when_within_30_min_of_open(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-23 08:45'));

        $s = $this->schedule(['open_am' => '09:00', 'close_am' => '12:00']);

        $this->assertSame('opening_soon', $s->status);
    }

    public function test_returns_closed_between_slots(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-23 13:00'));

        $s = $this->schedule([
            'open_am' => '09:00', 'close_am' => '12:00',
            'open_pm' => '14:00', 'close_pm' => '18:00',
        ]);

        $this->assertSame('closed', $s->status);
    }

    public function test_returns_open_in_pm_slot(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-23 15:00'));

        $s = $this->schedule([
            'open_am' => '09:00', 'close_am' => '12:00',
            'open_pm' => '14:00', 'close_pm' => '18:00',
        ]);

        $this->assertSame('open', $s->status);
    }

    public function test_returns_closed_when_no_slots_defined(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-23 10:00'));

        $s = $this->schedule([]);

        $this->assertSame('closed', $s->status);
    }
}
