@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="panel panel-default">
                    <div class="panel-heading">So you want to provision '<strong>{{ $repo }}</strong>' @if (!empty($timeEstimate)) &bull; ~{{ $timeEstimate }} seconds @endif</div>

                    <div class="panel-body">
                        <h1>Description</h1>
                        <blockquote>
                            <p>{{ $description }}</p>
                        </blockquote>

                        <a href="#" class="btn btn-default btn-small" id="view-provisionerScript">View provisioner script</a>
                        <a href="#" class="btn btn-default btn-small" id="view-fodorJson">View fodor.json</a>
                        <a href="https://twitter.com/intent/tweet?text=Provision+{{ $repo }}+@if (!empty($timeEstimate))in ~{{ $timeEstimate }} seconds @endif at https://fodor.xyz/provision/{{ $repo }} @fodorxyz" target="_blank" type="button" class="btn btn-info">Tweet</a>
                        <a href="https://github.com/{{ $repo }}" target="_blank" type="button" class="btn btn-primary">GitHub</a>

                        <hr />

                        <a href="/provision/start/{{ $repo }}" class="btn btn-lg btn-success">Let's do this</a>

                        <pre id="provisionerScript" class="hidden">{{ $provisionerScript }}</pre>
                        <pre id="fodorJson" class="hidden">{{ $fodorJson }}</pre>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
