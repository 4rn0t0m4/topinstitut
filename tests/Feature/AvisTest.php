<?php

namespace Tests\Feature;

use App\Models\Etablissement;
use App\Models\User;
use Tests\TestCase;

class AvisTest extends TestCase
{
    public function test_authenticated_user_can_submit_avis(): void
    {
        $user = User::first();
        $etab = Etablissement::valide()->first();
        if (! $user || ! $etab) {
            $this->markTestSkipped('No user or etablissement');
        }

        $this->actingAs($user)
            ->post('/avis', [
                'etablissement_id' => $etab->id,
                'titre' => 'Test avis',
                'contenu' => 'Contenu du test avis automatisé',
                'note_accueil' => 4,
                'note_qualite' => 4,
                'note_choix' => 3,
                'note_prix' => 3,
                'note_cadre' => 5,
                'note_proprete' => 5,
            ])
            ->assertRedirect();
    }

    public function test_anonymous_can_submit_avis_with_email(): void
    {
        $etab = Etablissement::valide()->first();
        if (! $etab) {
            $this->markTestSkipped('No etablissement');
        }

        $this->post('/avis', [
            'etablissement_id' => $etab->id,
            'pseudo_auteur' => 'TestAnon',
            'email_auteur' => 'anon-test-'.time().'@example.com',
            'titre' => 'Test anonyme',
            'contenu' => 'Avis anonyme avec confirmation email',
            'note_accueil' => 5,
            'note_qualite' => 4,
            'note_choix' => 4,
            'note_prix' => 3,
            'note_cadre' => 5,
            'note_proprete' => 5,
        ])
            ->assertRedirect();
    }

    public function test_anonymous_avis_requires_email(): void
    {
        $etab = Etablissement::valide()->first();
        if (! $etab) {
            $this->markTestSkipped('No etablissement');
        }

        $this->post('/avis', [
            'etablissement_id' => $etab->id,
            'titre' => 'Test sans email',
            'contenu' => 'Avis sans pseudo ni email',
            'note_accueil' => 5,
            'note_qualite' => 4,
            'note_choix' => 4,
            'note_prix' => 3,
            'note_cadre' => 5,
            'note_proprete' => 5,
        ])
            ->assertSessionHasErrors(['pseudo_auteur', 'email_auteur']);
    }
}
