@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="panel panel-default">
                    <div class="panel-heading">Let's provision '<strong>{{ $repo }}</strong>' on a new DigitalOcean droplet @if (!empty($timeEstimate)) in roughly ~{{ $timeEstimate }} seconds @endif</div>

                    <div class="panel-body">
                        <h1>Description</h1>
                        <blockquote>
                            <p>{{ $description }}</p>
                        </blockquote>

                        @if (!empty($imageUrl))
                            <h1>Image</h1>
                            <img src="{{ $imageUrl }}" class="img-responsive" alt="Image of {{ $repo }}"/>
                            <br />
                        @endif

                        <div class="btn-group" role="group">
                            <a href="#" class="btn btn-default btn-small" id="view-provisionerScript">View provisioner script</a>
                            <a href="#" class="btn btn-default btn-small" id="view-fodorJson">View fodor.json</a>
                        </div>

                        <div class="visible-xs">&nbsp;</div>

                        <div class="btn-group" role="group">
                            <a href="https://twitter.com/intent/tweet?text=Provision+{{ $repo }}+@if (!empty($timeEstimate))in ~{{ $timeEstimate }} seconds @endif at https://fodor.xyz/provision/{{ $repo }} @fodorxyz" target="_blank" type="button" class="btn btn-info">Tweet</a>
                            <a href="https://github.com/{{ $repo }}" target="_blank" type="button" class="btn btn-primary">GitHub</a>

                        @if (!empty($homepage))
                            <a href="{{$homepage}}" target="_blank" type="button" class="btn btn-warning">Project Homepage</a>
                        @endif
                        </div>

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
