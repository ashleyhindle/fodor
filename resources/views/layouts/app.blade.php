<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Fodor.xyz</title>

    <meta property="og:site_name" content="https://fodor.xyz" />
    <meta property="og:type" content="website" />
    <meta property="og:title" content="Fodor.xyz" />
    <meta property="og:description" content="Auto setup and provision GitHub repositories on a new DigitalOcean droplet" />
    <meta property="og:url" content="https://fodor.xyz" />
    <meta property="og:image" content="https://fodor.xyz/images/fodor-square-logo.png" />

    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:site" content="@ashleyhindle" />
    <meta name="twitter:creator" content="@ashleyhindle" />
    <meta name="twitter:title" content="Fodor.xyz" />
    <meta name="twitter:description" content="Auto setup and provision GitHub repositories on a new DigitalOcean droplet" />
    <meta name="twitter:url" content="https://fodor.xyz" />
    <meta name="twitter:image:src" content="https://fodor.xyz/images/fodor-square-logo.png" />


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
                <a class="navbar-brand" href="{{ url('/') }}">
                    Fodor
                </a>
            </div>

            <div class="collapse navbar-collapse" id="app-navbar-collapse">
                <!-- Left Side Of Navbar -->
                <ul class="nav navbar-nav">
                    {{-- <li><a href="{{ url('/') }}">Home</a></li> --}}
                </ul>

                <!-- Right Side Of Navbar -->
                <ul class="nav navbar-nav navbar-right">
                    <!-- Authentication Links -->
                    <li><a href="mailto:ashley@fodor.xyz">Get in touch</a></li>
                    {{--
                    @if (Auth::guest())
                        <li><a href="{{ url('/login') }}">Login</a></li>
                        <li><a href="{{ url('/register') }}">Register</a></li>
                    @else
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                                {{ Auth::user()->name }} <span class="caret"></span>
                            </a>

                            <ul class="dropdown-menu" role="menu">
                                <li><a href="{{ url('/logout') }}"><i class="fa fa-btn fa-sign-out"></i>Logout</a></li>
                            </ul>
                        </li>
                    @endif
                    --}}
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

    <!-- JavaScripts -->
    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/zeroclipboard/2.2.0/ZeroClipboard.min.js"></script>
    <script src="{{ elixir('js/app.js') }}"></script>
</body>
</html>
