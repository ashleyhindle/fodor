@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="panel panel-default">
                    <div class="panel-heading">Drum roll please! It's done. Finished. Complete...</div>

                    <div class="panel-body">
                        <h2>All done! Phew! Details below:</h2>

                        <strong>IP: </strong> {{ $ip }}<br />
                        <strong>Domain: </strong> {{ $domain }}<br />
                        <strong>SSH: </strong><a href="ssh://root{{ '@' . $domain }}">ssh root{{ '@' . $domain }}</a><br />
                        <strong>HTTP: </strong><a target="_blank" href="http://{{ $domain }}">{{ $domain }}</a><br />
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
