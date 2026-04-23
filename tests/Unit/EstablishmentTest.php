<?php

namespace Tests\Unit;

use App\Models\Establishment;
use PHPUnit\Framework\TestCase;

class EstablishmentTest extends TestCase
{
    public function test_type_id_from_slug_returns_integer_for_known_slugs(): void
    {
        $this->assertSame(0, Establishment::typeIdFromSlug('institut-de-beaute'));
        $this->assertSame(1, Establishment::typeIdFromSlug('estheticienne-a-domicile'));
        $this->assertSame(2, Establishment::typeIdFromSlug('spa'));
        $this->assertSame(3, Establishment::typeIdFromSlug('thalasso'));
    }

    public function test_type_id_from_slug_returns_null_for_unknown(): void
    {
        $this->assertNull(Establishment::typeIdFromSlug('unknown'));
        $this->assertNull(Establishment::typeIdFromSlug(''));
    }

    public function test_normalize_services_trims_and_filters_empty(): void
    {
        $input = [
            ['name' => '  Manucure  ', 'price' => ' 25€ ', 'duration' => ' 30 min ', 'description' => ''],
            ['name' => '', 'price' => '10€'], // dropped
            ['name' => 'Massage', 'description' => ' Relaxant '],
        ];

        $result = Establishment::normalizeServices($input);

        $this->assertCount(2, $result);
        $this->assertSame('Manucure', $result[0]['name']);
        $this->assertSame('25€', $result[0]['price']);
        $this->assertSame('Massage', $result[1]['name']);
        $this->assertSame('Relaxant', $result[1]['description']);
    }

    public function test_normalize_services_returns_null_for_empty_input(): void
    {
        $this->assertNull(Establishment::normalizeServices([]));
        $this->assertNull(Establishment::normalizeServices([['name' => '']]));
    }
}
