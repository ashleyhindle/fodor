@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-10 col-md-offset-1">
            <div class="panel panel-default">
                <div class="panel-heading">We're nearly there..</div>

                <div class="panel-body">
                    <h1>Well howdy!</h1>

                    So we provide the environment variables: $INSTALLPATH (from fodor.json), $NAME (repo name - ashleyhindle/fodor-graylog2), $GITURL (https://github.com/${NAME}.git - TODO: Support gitlab.com, very important)

                    <img src="/images/deploy-with-fodor-225x70.png"/>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
