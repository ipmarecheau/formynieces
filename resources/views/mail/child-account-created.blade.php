<x-mail::message>
# {{ $child->name }} is all set to sail 🐢

Their SmoothSeas account is ready. Here is the **login ID** to keep on file:

<x-mail::panel>
**{{ $loginId }}**
</x-mail::panel>

For safety, we don't email passwords. You can **reveal or reset {{ $child->name }}'s password anytime** from your Parent Portal:

<x-mail::button :url="$manageUrl">
Manage {{ $child->name }}'s login
</x-mail::button>

Fair winds,<br>
The SmoothSeas crew
</x-mail::message>
