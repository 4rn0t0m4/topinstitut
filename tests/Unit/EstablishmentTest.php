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
}
