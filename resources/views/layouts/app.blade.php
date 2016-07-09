<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Fodor.xyz</title>

    <meta property="og:site_name" content="https://fodor.xyz" />
    <meta property="og:type" content="website" />
    <meta property="og:title" content="@if(isset($description))Auto provision {{ $repo or 'GitHub repositories' }} on a new DigitalOcean {{'droplet'}}@else{{'Fodor.xyz'}}@endif" />
    <meta property="og:description" content="@if(!isset($description))Auto setup and provision {{ $repo or 'GitHub repositories' }} on a new DigitalOcean droplet @else{{$description}}@endif" />
    <meta property="og:url" content="https://fodor.xyz" />
    <meta property="og:image" content="https://fodor.xyz/images/fodor-square-logo.png" />

    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:site" content="@ashleyhindle" />
    <meta name="twitter:creator" content="@ashleyhindle" />
    <meta name="twitter:title" content="@if(isset($description))Auto provision {{ $repo or 'GitHub repositories' }} on a new DigitalOcean {{'droplet'}}@else{{'Fodor.xyz'}}@endif" />
    <meta name="twitter:description" content="@if(!isset($description))Auto setup and provision {{ $repo or 'GitHub repositories' }} on a new DigitalOcean droplet @else{{$description}}@endif" />
    <meta name="twitter:url" content="https://fodor.xyz" />
    <meta name="twitter:image:src" content="https://fodor.xyz/images/fodor-square-logo.png" />

    <link rel="apple-touch-icon-precomposed" sizes="57x57" href="/images/favicon/apple-touch-icon-57x57.png" />
    <link rel="apple-touch-icon-precomposed" sizes="114x114" href="/images/favicon/apple-touch-icon-114x114.png" />
    <link rel="apple-touch-icon-precomposed" sizes="72x72" href="/images/favicon/apple-touch-icon-72x72.png" />
    <link rel="apple-touch-icon-precomposed" sizes="144x144" href="/images/favicon/apple-touch-icon-144x144.png" />
    <link rel="apple-touch-icon-precomposed" sizes="120x120" href="/images/favicon/apple-touch-icon-120x120.png" />
    <link rel="apple-touch-icon-precomposed" sizes="152x152" href="/images/favicon/apple-touch-icon-152x152.png" />
    <link rel="icon" type="image/png" href="/images/favicon/favicon-32x32.png" sizes="32x32" />
    <link rel="icon" type="image/png" href="/images/favicon/favicon-16x16.png" sizes="16x16" />
    <meta name="application-name" content="&nbsp;"/>
    <meta name="msapplication-TileColor" content="#FFFFFF" />
    <meta name="msapplication-TileImage" content=/images/favicon/"mstile-144x144.png" />


    <!-- Fonts -->
    <link href="//cdnjs.cloudflare.com/ajax/libs/font-awesome/4.4.0/css/font-awesome.min.css" rel='stylesheet' type='text/css'>
    <!-- Styles -->
    <link href="{{ elixir('css/app.css') }}" rel="stylesheet">
</head>
<body id="app-layout">
    <nav class="navbar navbar-default">
        <div class="container">
            <div class="navbar-header">

                <!-- Collapsed Hamburger -->
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#app-navbar-collapse">
                    <span class="sr-only">Toggle Navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>

                <!-- Branding Image -->
                <a class="navbar-brand" href="{{ url('/') }}">Fodor</a>
                    <span class="navbar-text"><small>Take control of your services and data</small></span>
            </div>

            <div class="collapse navbar-collapse" id="app-navbar-collapse">
                <!-- Left Side Of Navbar -->
                <ul class="nav navbar-nav">
                    {{-- <li><a href="{{ url('/') }}">Home</a></li> --}}
                </ul>

                <!-- Right Side Of Navbar -->
                <ul class="nav navbar-nav navbar-right">
                    <li><a href="mailto:ashley@fodor.xyz">Get in touch</a></li>
                    {{--
                    @if (Auth::guest())
                        <li><a href="{{ url('/login') }}">Login</a></li>
                        <li><a href="{{ url('/register') }}">Register</a></li>
                    @else
                    --}}
                    @if (Session::has('digitalocean.email'))
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                            {{ Session::get('digitalocean.email') }}<span class="caret"></span>
                        </a>

                        <ul class="dropdown-menu" role="menu">
                            <li><a href="{{ url('/do/logout') }}"><i class="fa fa-btn fa-sign-out"></i>Logout</a></li>
                        </ul>
                    </li>
                    @endif
                </ul>
            </div>
        </div>
    </nav>

    <div class="container flash-message">
        @if(Session::has('flash.new'))
            @foreach (Session::get('flash.new') as $flashKey)
                <p class="alert alert-{{ Session::get($flashKey)['type'] }}">
                    {{ Session::get($flashKey)['message'] }}
                    <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                </p>
            @endforeach
        @endif

        @if(Session::has('flash.old'))
            @foreach (Session::get('flash.old') as $flashKey)
                <p class="alert alert-{{ Session::get($flashKey)['type'] }}">
                    {{ Session::get($flashKey)['message'] }}
                    <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                </p>
            @endforeach
        @endif
    </div> <!-- end .flash-message -->

    @yield('content')

        <ul id="footer" class="nav nav-pills list-inline" style="border-top: 1px solid #CCC;">
            <li class="pull-right">
                <a href="https://twitter.com/ashleyhindle">Made by @ashleyhindle</a>
            </li>
            <li class="pull-right">
                <a href="https://github.com/fodorxyz/fodor">GitHub</a>
            </li>

            <li class="pull-right">
                <a href="https://raw.githubusercontent.com/ashleyhindle/fodor-example/master/README.md">Example markdown image</a>
            </li>
        </ul>

    <div class="row text-center">
        <h4>Sponsored by</h4>
        <a href="https://m.do.co/c/c2c89916e190"><img src="/images/sponsored-by-digitalocean.png" width="300px" height="48px"/></a>
        <a href="https://siftware.com"><img src="/images/sponsored-by-siftware.jpg" width="300px" height="100px"/></a>
    </div>

    <!-- JavaScripts -->
    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
    <script src="{{ elixir('js/clipboard.min.js') }}"></script>
    <script src="{{ elixir('js/app.js') }}"></script>

    <script>
        (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
                    (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
                m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
        })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

        ga('create', 'UA-75268624-1', 'auto');
        ga('send', 'pageview');

    </script>
</body>
</html>
