@component('mail::message')
# Account Approved

Dear {{ $staff->first_name }},

Your staff account for **PUPSJ Libris** has been approved.
You may now log in using your employee ID and password.

Thanks,<br>
PUPSJ Libris
@endcomponent
