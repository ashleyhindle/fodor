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

                        <h3>Links</h3>
                        @forelse ($links as $link)
                            <strong>{{ $link['title'] }}: </strong><a target="_blank" href="{{ $link['url'] }}">{{ $link['url'] }}</a><br />
                        @empty
                            No links provided
                        @endforlse
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
