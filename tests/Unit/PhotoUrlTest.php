<?php

namespace Tests\Unit;

use App\Models\Photo;
use Tests\TestCase;

class PhotoUrlTest extends TestCase
{
    public function test_url_accessor_combines_r2_base_url_and_path(): void
    {
        config()->set('filesystems.disks.r2.url', 'https://cdn.example.com');

        $photo = new Photo(['establishment_id' => 42, 'filename' => 'google_1.jpg']);

        $this->assertSame('https://cdn.example.com/etablissements/42/google_1.jpg', $photo->url);
    }

    public function test_url_strips_trailing_slash_from_base(): void
    {
        config()->set('filesystems.disks.r2.url', 'https://cdn.example.com/');

        $photo = new Photo(['establishment_id' => 1, 'filename' => 'x.jpg']);

        $this->assertSame('https://cdn.example.com/etablissements/1/x.jpg', $photo->url);
    }
}
