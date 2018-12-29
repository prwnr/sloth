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
    <link rel="stylesheet" href="//code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body class="hold-transition auth-page">
<div id="app">
    <section class="login-block">
        <div class="container">
            <div class="row">
                <div class="col-md-4 login-sec">
                    @yield('content')
                </div>
                <div class="col-md-8 banner-sec">
                    <div id="slothCarousel" class="carousel slide" data-ride="carousel">
                        <ul class="carousel-indicators">
                            <li data-target="#slothCarousel" data-slide-to="0" class="active"></li>
                            <li data-target="#slothCarousel" data-slide-to="1"></li>
                            <li data-target="#slothCarousel" data-slide-to="2"></li>
                        </ul>
                        <div class="carousel-inner" role="listbox">
                            <div class="carousel-item active">
                                <div class="carousel-placeholder">
                                    <i class="fa fa-clock-o"></i>
                                </div>
                                <div class="carousel-caption d-none d-md-block">
                                    <div class="banner-text">
                                        <h2>Slothy?</h2>
                                        <p>
                                            At work we should not be <em>Slothful</em>, but why don't we make some things
                                            easier?
                                        </p>
                                        <p>
                                            <b>Sloth</b> is a time tracking application.
                                        </p>
                                        <p>
                                            With <b>Sloth</b> You can track your own worked time.
                                            You can even create and manage your clients, projects and members and you
                                            can keep an eye on all of that. It's simple!
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="carousel-item">
                                <div class="carousel-placeholder">
                                    <i class="fa fa-users"></i>
                                </div>
                                <div class="carousel-caption d-none d-md-block">
                                    <div class="banner-text">
                                        <h2>More Sloths?</h2>
                                        <p>
                                            <b>Sloth</b> not only allows you to create a team and your own members.
                                            It allows others to create their own teams, and add you as their member.
                                        </p>
                                        <p>
                                            <b>Sloth</b> is not limiting you to be in one team. It is possible
                                            to have account assigned to multiple teams, what makes switching between them
                                            very simple, just by one click. No need to create multiple accounts, use
                                            just one!
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="carousel-item">
                                <div class="carousel-placeholder">
                                    <i class="fa fa-laptop"></i>
                                </div>
                                <div class="carousel-caption d-none d-md-block">
                                    <div class="banner-text">
                                        <h2>Be relieved!</h2>
                                        <p>
                                            <b>Sloth</b> comes with usefull <em>slothful</em> features.
                                        </p>
                                        <ul class="list-unstyled">
                                            <li>Clients management with budget and billings,</li>
                                            <li>Projects management with budget and billings,</li>
                                            <li>Your own team members with salary,</li>
                                            <li>Roles and permissions management for your members,</li>
                                            <li>Detailed reports for everything that is tracked,</li>
                                            <li>List of todo tasks,</li>
                                            <li>and of course, time tracker.</li>
                                        </ul>
                                        <p>Create your team and find out more!</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script src="{{ asset('js/app.js') }}"></script>
@stack('scripts')
</body>
</html>
