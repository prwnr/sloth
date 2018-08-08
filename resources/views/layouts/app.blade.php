<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">
    <!-- IonIcons -->
    <link rel="stylesheet" href="code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <!-- Google Font: Source Sans Pro -->
</head>
<body class="hold-transition sidebar-mini sticky-footer">
<div id="app">
    <auth-user :user="{{ json_encode($activeUser) }}"></auth-user>
    <div class="wrapper">
        @include('blocks/navbar')
        @include('blocks/sidebar')

        <div class="content-wrapper">
            <router-view></router-view>
        </div>

        @include('blocks/footer')
        @include('blocks/control-sidebar')
    </div>
</div>
<script src="{{ asset('js/app.js') }}"></script>
<script src="{{ asset('js/adminlte.js') }}"></script>
</body>
</html>
