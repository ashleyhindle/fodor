@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="panel panel-default">
                    <div class="panel-heading">Waiting for droplet to be created..</div>

                    <div class="panel-body">
                        We're just waiting on DigitalOcean to create the droplet so we can provision it.  Give us two shakes of a lamb's tail!
                        <div class="progress">
                            <div id="waitingProgress" data-id={{ $id }} data-uuid="{{ $uuid }}" class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="45" aria-valuemin="0" aria-valuemax="100" style="width: 20%">
                                <span class="sr-only">20% Complete</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
