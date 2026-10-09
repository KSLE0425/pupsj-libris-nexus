@component('mail::message')
# Your Reserved Book is Now Available

Dear {{ $reservation->reserver_name }},

The book **"{{ $book->title }}"** that you reserved is now available for borrowing.
Please visit the library to pick it up.

@component('mail::button', ['url' => url('/login')])
Log in to your account
@endcomponent

Thank you,<br>
PUPSJ Libris
@endcomponent