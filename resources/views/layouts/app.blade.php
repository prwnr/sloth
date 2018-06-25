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
    <link rel="stylesheet" href="http://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <!-- Google Font: Source Sans Pro -->
</head>
<body class="hold-transition sidebar-mini">
<div id="app" class="wrapper">
    <auth-user :user="{{ json_encode($activeUser) }}"></auth-user>
    @include('blocks/navbar')
    @include('blocks/sidebar')

    <div class="content-wrapper">
        <router-view></router-view>
    </div>

    @include('blocks/control-sidebar')
    @include('blocks/footer')
</div>
<!-- ./wrapper -->

<!-- REQUIRED SCRIPTS -->

<script src="{{ asset('js/app.js') }}"></script>
<script src="{{ asset('js/adminlte.js') }}"></script>
@stack('scripts')
</body>
</html>
