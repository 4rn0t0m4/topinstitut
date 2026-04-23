<x-mail::message>
# Bonjour {{ $reminder->name ?? '' }},

Vous avez récemment pris contact avec **{{ $establishment->name }}** via TopInstitut. Nous espérons que votre visite s'est bien passée !

Partagez votre expérience pour aider d'autres clientes et clients à choisir le bon institut. Votre avis prend 2 minutes et fait toute la différence.

<x-mail::button :url="$reviewUrl" color="primary">
Laisser mon avis
</x-mail::button>

Si vous n'avez finalement pas visité cet établissement, ignorez simplement cet email.

Merci,<br>
L'équipe TopInstitut
</x-mail::message>
