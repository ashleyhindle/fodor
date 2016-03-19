@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="panel panel-default">
                    <div class="panel-heading">Setting up <strong>{{ $provision['repo'] }}</strong> on your server...</div>

                    <div class="panel-body">
                        <h2>Running scripts on your fresh server...</h2>

                        <div id="erroredInfo" class="hidden">
                            <div class="alert alert-danger" role="alert">The provisioning script has very likely failed</div>

                            <h3>Options</h3>
                            <div class="btn-group" role="group">
                                <a href="{{ url("/provision/logDownload/{$id}/{$uuid}") }}" type="button" class="btn btn-default">Download provisioning log</a>
                                <a href="https://github.com/{{ $provision['repo'] }}/issues/new?title=Fodor+provisioning+failed&body=Here+is+my+log+file" target="_blank" type="button" class="btn btn-primary">Add GitHub issue</a>
                                <a href="{{ url("/provision/ready/{$id}/{$uuid}") }}" type="button" class="btn btn-warning" data-toggle="tooltip" title="I know better than a stinkin' computer!">Ignore failure</a>
                                <a href="https://cloud.digitalocean.com/droplets/{{ $provision['dropletid'] }}/destroy" type="button" class="btn btn-danger">Destroy droplet</a>
                            </div>
                        </div>

                        <div id="provisioningLog" data-id={{ $id }} data-uuid="{{ $uuid }}">

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
