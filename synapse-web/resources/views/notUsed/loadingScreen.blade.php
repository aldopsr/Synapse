<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'Laravel') }}</title>
        <link rel="stylesheet" href="{{asset('assets/css/style.css')}}">
    </head>
    <body class="center-container" style="height:100vh; background-color: #279685; gap: 40px 0px;">
        <!-- Logo Synapse -->
        <img src="{{asset('assets/image/synapseLogo.png')}}" style="width: 221px; height: auto;">

        <!-- Judul -->
        <div style="display: flex; flex-direction: column; align-items:center ;justify-content:center; gap: 8px 0px">
            <h1 style="color: #EDEFF1; font-size: 63px; font-weight: 600;">SYNAPSE</h1>
        </div>
    </body>
</html>
