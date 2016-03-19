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
                                <p class="list-group-item-text btn-copy" data-clipboard-text="{{ $domain }}">{{ $domain }}</p>
                            </li>
                            @if (strlen($provision['rootPassword']) > 0)
                                <li class="list-group-item">
                                    <h4 class="list-group-item-heading">Root Password</h4>
                                    <p class="list-group-item-text btn-copy" data-clipboard-text="{{ $provision['rootPassword'] }}">{{ $provision['rootPassword'] }}</p>
                                </li>
                            @endif

                            @if (!empty($successText))
                                <li class="list-group-item">
                                    <h4 class="list-group-item-heading">Extra information:</h4>
                                    <p class="list-group-item-text">{{ $successText }}</p>
                                </li>
                            @endif
                        </ul>

                        @if (strlen($provision['rootPassword']) > 0)
                            <span class="label label-warning">The root password has been deleted from our database - we can't show you this again, don't lose it!</span><br />
                        @endif

                        <div class="list-group">
                            <a href="#" class="list-group-item active">
                                Cras justo odio
                            </a>
                            <a href="#" class="list-group-item">Dapibus ac facilisis in</a>
                            <a href="#" class="list-group-item">Morbi leo risus</a>
                            <a href="#" class="list-group-item">Porta ac consectetur ac</a>
                            <a href="#" class="list-group-item">Vestibulum at eros</a>
                        </div>

                        <h3>Links</h3>
                        <div class="list-group">
                        @forelse ($links as $link)
                                <a target="_blank" class="list-group-item" href="{{ $link['url'] }}">
                                    <strong>{{ $link['title'] }}: </strong>{{ $link['url'] }}
                                </a>
                        @empty
                            No links provided
                        @endforelse
                        </div>

                        <a href="{{ url("/provision/logDownload/{$provision['id']}/{$provision['uuid']}") }}" type="button" class="btn btn-default">Download provisioning log</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
