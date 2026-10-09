@component('mail::message')
# Book Available

Dear {{ $notification->notifier_name }},

The book **"{{ $book->title }}"** that you were interested in is now available for borrowing.

Please visit the library to borrow it.

@component('mail::button', ['url' => url('/login')])
Log in to your account
@endcomponent

Thank you,<br>
PUPSJ Libris
@endcomponent