@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="panel panel-default">
                    <div class="panel-heading">Provisioning '{{ $repo }}' - which SSH keys shall we add?</div>

                    <div class="panel-body">
                        <form action="{{ url('provision/doit') }}" method="POST">
                            {{ csrf_field() }}
                            <input type="hidden" name="provisionid" value="{{ $provisionid }}" />
                            <input type="hidden" name="name" value="{{ $repo }}" />
                            <input type="hidden" name="size" value="{{ $size }}" /> {{-- TODO: Give option --}}
                            <input type="hidden" name="region" value="ams2" /> {{-- TODO: Give option --}}
                            <input type="hidden" name="distro" value="{{ $distro }}" />

                            @forelse ($keys as $keyId => $keyName)
                                <li>
                                    <label for="keys[{{$keyId}}">{{$keyName}}</label> <input type="checkbox" name="keys[{{$keyId}}]" value="{{$keyId}}" checked="checked" />
                                </li>
                            @empty
                                <p>No SSH keys to add - we'll set you a root password</p>
                            @endforelse

                            <input type="submit">
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
