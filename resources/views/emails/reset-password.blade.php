@extends('emails.base')

@section('content')
    <tr>
        <td colspan="2" style="padding: 10px 20px; color: #333333; background-color: #F9F9F9;">
            <h2 style="margin: 0; color: #212121;">Olá {{ $user->name }}!</h2>
        </td>
    </tr>
    <tr>
        <td colspan="2" style="padding: 10px 20px 0; color: #333333; background-color: #F9F9F9;">
            <p style="margin: 10px 0; line-height: 1.6;">Uma nova solicitação de redefinição de senha foi recebida para sua
                conta.</p>
            <div style="text-align: center; margin: 20px 0; font-size: 32px; font-weight: bold; letter-spacing: 5px;">
                {{ $code }}
            </div>
        </td>
    </tr>
@endsection