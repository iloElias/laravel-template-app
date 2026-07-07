@extends('emails.base')

@section('content')
<tr>
    <td colspan="2" style="padding: 10px 20px; color: #333333; background-color: #F9F9F9;">
        <h2 style="margin: 0; color: #212121;">Novo acesso detectado na rota /access</h2>
        <p style="margin: 10px 0 0; line-height: 1.5; color: #666666; font-size: 14px;">
            O acesso foi salvo com sucesso no banco de dados.
        </p>
    </td>
</tr>

<tr>
    <td colspan="2" style="padding: 10px 20px; color: #333333; background-color: #F9F9F9;">
        <h3 style="margin: 0 0 8px; color: #212121;">Resumo</h3>
        <p style="margin: 0 0 6px;"><strong>Request ID:</strong> {{ $access['request_id'] ?? '-' }}</p>
        <p style="margin: 0 0 6px;"><strong>Source ID:</strong> {{ $access['source_id'] ?? '-' }}</p>
        <p style="margin: 0 0 6px;"><strong>IP:</strong> {{ $access['ip'] ?? '-' }}</p>
        <p style="margin: 0 0 6px;"><strong>User Agent:</strong> {{ $access['user_agent'] ?? '-' }}</p>
        <p style="margin: 0 0 6px;"><strong>URL:</strong> {{ $access['full_url'] ?? '-' }}</p>
        <p style="margin: 0;"><strong>Criado em:</strong> {{ $access['created_at'] ?? '-' }}</p>
    </td>
</tr>

<tr>
    <td colspan="2" style="padding: 10px 20px; color: #333333; background-color: #F9F9F9;">
        <h3 style="margin: 0 0 8px; color: #212121;">Localizacao aproximada por IP</h3>
        <p style="margin: 0 0 6px;"><strong>Cidade:</strong> {{ $geo['city'] ?? '-' }}</p>
        <p style="margin: 0 0 6px;"><strong>Regiao:</strong> {{ $geo['region'] ?? '-' }}</p>
        <p style="margin: 0 0 6px;"><strong>Pais:</strong> {{ $geo['country'] ?? '-' }} ({{ $geo['country_code'] ?? '-' }})</p>
        <p style="margin: 0 0 12px;"><strong>Coordenadas:</strong> {{ $geo['latitude'] ?? '-' }}, {{ $geo['longitude'] ?? '-' }}</p>

        @if($mapImageUrl)
            @if($mapOpenUrl)
                <a href="{{ $mapOpenUrl }}" target="_blank" style="display: block; margin-bottom: 10px; color: #1a73e8; text-decoration: none;">Abrir mapa completo</a>
            @endif
            <img src="{{ $mapImageUrl }}" alt="Mapa aproximado do IP" style="max-width: 100%; border: 1px solid #DDDDDD; border-radius: 8px;">
        @else
            <p style="margin: 0; color: #666666; font-size: 14px;">Nao foi possivel determinar coordenadas publicas para gerar o mapa.</p>
        @endif
    </td>
</tr>
@endsection
