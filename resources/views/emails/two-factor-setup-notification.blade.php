@extends('emails.base')

@section('content')
<tr>
    <td colspan="2" style="padding: 10px 20px; background-color: #F9F9F9; color: #333333;">
        <h2 style="margin: 0; color: #212121;">{{ __('email.two_factor_setup_notification.title') }}</h2>
    </td>
</tr>
<tr>
    <td colspan="2" style="padding: 10px 20px; background-color: #F9F9F9; color: #333333;">
        <p style="margin: 10px 0; line-height: 1.6;">
            {{ __('email.two_factor_setup_notification.message') }}
        </p>
        <div style="text-align: center; margin: 20px 0; font-size: 32px; font-weight: bold; letter-spacing: 5px;">
            {{ $info['code'] }}
        </div>
        <p style="margin: 10px 0; line-height: 1.6; font-size: 14px; color: #666666;">
            {{ __('email.two_factor_setup_notification.expiration', ['expires' => $info['expires']]) }}
        </p>
    </td>
</tr>
@endsection
