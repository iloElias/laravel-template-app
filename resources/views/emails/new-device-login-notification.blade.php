@extends('emails.base')

@section('content')
<tr>
    <td colspan="2" style="padding: 10px 20px; background-color: #F9F9F9; color: #333333;">
        <h2 style="margin: 0; color: #212121;">{{ __('email.new_device_login_notification.title') }}</h2>
    </td>
</tr>
<tr>
    <td colspan="2" style="padding: 10px 20px; background-color: #F9F9F9; color: #333333;">
        <p style="margin: 10px 0; line-height: 1.6;">
            {{ __('email.new_device_login_notification.message') }}
        </p>
    </td>
</tr>
@endsection
