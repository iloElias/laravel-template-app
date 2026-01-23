@extends('emails.base')

@section('content')
<tr>
    <td colspan="2" style="padding: 10px 20px; color: #333333; background-color: #F9F9F9;">
        <h2 style="margin: 0; color: #212121;">{{ __('email.first_login.title') }}</h2>
    </td>
</tr>
<tr>
    <td colspan="2" style="padding: 10px 20px 0; color: #333333; background-color: #F9F9F9;">
        <p style="margin: 10px 0; line-height: 1.6;">{{ __('email.first_login.message') }}</p>
        <div style="text-align: center; margin: 20px 0; font-size: 32px; font-weight: bold; letter-spacing: 5px;">
            {{ $info['code'] }}
        </div>
    </td>
</tr>
<tr>
    <td colspan="2" style="padding: 0 20px 10px; color: #333333; background-color: #F9F9F9;">
        <!-- <p style="margin: 0 0 10px; line-height: 1.6;">Clique no botão abaixo para confirmar seu acesso:</p>
        <div style="text-align: center;">
            <a href="{{ url(env('WEB_URL') . env('WEB_AUTH_PAGE_URL') . '?code=' . $info['code']) }}" style="display: inline-block; padding: 10px 20px; margin: 20px 0; background-color: #33cf5e; color: #fff; text-decoration: none; border-radius: 12px;">
                {{ __('email.first_login.confirm_button') }}
            </a>
        </div> -->
        <p style="margin: 10px 0; line-height: 1.6; font-size: 14px; color: #666666;">
            {{ __('email.first_login.expiration', ['expires' => $info['expires']]) }}
        </p>
    </td>
</tr>
@endsection
