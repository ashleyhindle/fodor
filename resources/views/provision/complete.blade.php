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
                                <h4 class="list-group-item-heading">
                                    IP
                                    <div data-clipboard-text="{{ $ip }}" class="pull-right btn-copy" aria-hidden="true">Copy</div>
                                </h4>
                                <p class="list-group-item-text">{{ $ip }}</p>
                            </li>

                            <li class="list-group-item">
                                <h4 class="list-group-item-heading">
                                    Domain
                                    <div data-clipboard-text="{{ $domain }}" class="pull-right btn-copy" aria-hidden="true">Copy</div>
                                </h4>
                                <p class="list-group-item-text">{{ $domain }}</p>
                            </li>

                            @if (!empty($successText))
                                <li class="list-group-item">
                                    <h4 class="list-group-item-heading">Extra information:</h4>
                                    <p class="list-group-item-text">{{ $successText }}</p>
                                </li>
                            @endif
                        </ul>

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
                        <a href="https://twitter.com/intent/tweet?text=I've just setup+{{ $repo }}+on @DigitalOcean at https://fodor.xyz/provision/{{ $repo }} with @fodorxyz - couldn't have been easier!" target="_blank" type="button" class="btn btn-info">Tweet</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
