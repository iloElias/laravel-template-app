@extends('emails.base')

@section('content')
<tr>
    <td colspan="2" style="padding: 10px 20px; color: #333333; background-color: #F9F9F9;">
        <h2 style="margin: 0; color: #212121;">Olá {{ $user->name }}!</h2>
    </td>
</tr>
<tr>
    <td colspan="2" style="padding: 10px 20px 0; color: #333333; background-color: #F9F9F9;">
        <p style="margin: 10px 0; line-height: 1.6;">
            Você acabou de realizar uma oferta com o valor mínimo. Portanto, sua oferta foi aceita automaticamente!
        </p>
    </td>
</tr>
@endsection
