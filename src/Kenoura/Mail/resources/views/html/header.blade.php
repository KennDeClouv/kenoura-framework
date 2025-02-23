@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
@if (trim($slot) === 'Kenoura')
<img src="https://kenoura.com/img/notification-logo.png" class="logo" alt="Kenoura Logo">
@else
{{ $slot }}
@endif
</a>
</td>
</tr>
