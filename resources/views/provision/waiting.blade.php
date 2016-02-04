@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="panel panel-default">
                    <div class="panel-heading">Waiting for droplet to be created..</div>

                    <div class="panel-body">
                        <h2>{{ $status }}</h2>

                        <a href="{{ Request::url() }}">Refresh..</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
