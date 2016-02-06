@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-10 col-md-offset-1">
            <div class="panel panel-default">
                <div class="panel-heading">We're nearly there..</div>

                <div class="panel-body">
                    <h1>Well howdy!</h1>

                    <img src="/images/deploy-with-fodor-225x70.png"/>
                    {{--
                    <form action="{{ url('/provision/start') }}" method="POST">
                        {{ csrf_field() }}
                        <input type="text" name="repo" value="ashleyhindle/fodor-example">
                        <input type="submit">
                    </form>
                    <hr />

                    <div class="btn-group" role="group">
                        <a class="btn btn-large btn-primary" href="{{ url('/do/start') }}">Login with DigitalOcean</a>
                        <a class="btn btn-large btn-success" href="{{ url('/github/start') }}">Login with GitHub</a>
                    </div>
                    --}}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
