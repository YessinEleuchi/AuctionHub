@component('mail::message')
# Bonjour {{ $user->first_name }},

Merci de vous être inscrit sur notre plateforme.

Veuillez cliquer sur le bouton ci-dessous pour vérifier votre adresse email :

@component('mail::button', ['url' => $activationLink])
Vérifier mon email
@endcomponent

Si vous n'avez pas créé de compte, ignorez ce message.

Merci,<br>
{{ config('app.name') }}
@endcomponent
