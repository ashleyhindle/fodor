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

                        <ul class="list-group">
                            <li class="list-group-item">
                                <h4 class="list-group-item-heading">IP</h4>
                                <p class="list-group-item-text btn-copy" data-clipboard-text="{{ $ip }}">{{ $ip }}</p>
                            </li>
                            <li class="list-group-item">
                                <h4 class="list-group-item-heading">Domain</h4>
                                <p class="list-group-item-text btn-copy">{{ $domain }}</p>
                            </li>
                            <li class="list-group-item">
                                <h4 class="list-group-item-heading">SSH</h4>
                                <p class="list-group-item-text btn-copy">ssh root{{ '@'.$domain }}</p>
                            </li>
                            @if (strlen($provision['rootPassword']) > 0)
                                <li class="list-group-item">
                                    <h4 class="list-group-item-heading">Root Password</h4>
                                    <p class="list-group-item-text btn-copy">{{ $provision['rootPassword'] }}</p>
                                </li>
                            @endif
                        </ul>
                        <span class="label label-warning">The root password has been deleted from our database - we can't show you this again, don't lose it!</span><br />

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
