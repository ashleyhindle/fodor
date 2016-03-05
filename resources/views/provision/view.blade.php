@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="panel panel-default">
                    <div class="panel-heading">So you want to provision '<strong>{{ $repo }}</strong>'</div>

                    <div class="panel-body">
                        <h1>Description</h1>
                        <blockquote>
                            <p>{{ $description }}</p>
                        </blockquote>

                        <pre id="provisionerScript">{{ $provisionerScript }}</pre>
                        <pre id="fodorJson">{{ $fodorJson }}</pre>

                        <a href="#" class="btn btn-default btn-small" id="view-provisionerScript">View provisioner script</a>
                        <a href="#" class="btn btn-default btn-small" id="view-fodorJson">View fodor.json</a>

                        <a href="/provision/start/{{ $repo }}" class="btn btn-success">Let's do this</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
