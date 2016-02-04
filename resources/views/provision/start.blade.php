@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="panel panel-default">
                    <div class="panel-heading">Provision '{{ $repo }}'</div>

                    <div class="panel-body">
                        <form action="{{ url('provision/doit') }}" method="POST">
                            {{ csrf_field() }}
                            <input name="name" value="{{ $repo }}" />
                            <input name="size" value="{{ $size }}" />
                            <input name="region" value="lon1" />
                            <input name="distro" value="{{ $distro }}" />
                            <input type="submit">
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
