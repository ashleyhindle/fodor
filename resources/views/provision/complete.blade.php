@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="panel panel-default">
                    <div class="panel-heading">Drum roll please! It's done. Finished. Complete...</div>

                    <div class="panel-body">
                        <h2>It's all setup! It's actually not, I'm lying.  Currently I added it to a job queue for a thinga to pick up.  Replace this when it's bettererererer</h2>

                        <strong>IP: </strong> {{ $ip }}<br />
                        <strong>SSH: </strong> ssh root{{ '@' . $domain }}<br />
                        <strong>HTTP: </strong><a target="_blank" href="http://{{ $domain }}">{{ $domain }}</a><br />
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
