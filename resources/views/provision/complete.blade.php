@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="panel panel-default">
                    <div class="panel-heading">Drum roll please! It's done. Finished. Complete...</div>

                    <div class="panel-body">
                        <p>
                            We have installed <strong>{{ $provision['repo'] }}</strong> on a <strong>{{ $provision['size'] }}</strong> droplet in <strong>{{ $provision['region'] }}</strong>
                        </p>

                        <strong>IP: </strong> {{ $ip }}<br />
                        <strong>Domain: </strong> {{ $domain }}<br />
                        <strong>SSH: </strong> ssh root@{{ $domain }} / <i>Password:</i>{{ $provision['rootPassword'] }}<br />

                        <h3>Links</h3>
                        @forelse ($links as $link)
                            <strong>{{ $link['title'] }}: </strong><a target="_blank" href="{{ $link['url'] }}">{{ $link['url'] }}</a><br />
                        @empty
                            No links provided
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
