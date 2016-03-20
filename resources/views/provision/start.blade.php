@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="panel panel-default">
                    <div class="panel-heading">Provisioning <code>{{ $repo }}</code> - {{ $description }} - <span class="label label-info">{{ $distro }}</span></div>

                    <div class="panel-body">
                        <form action="{{ url("/provision/doit") }}" method="POST">
                            {{ csrf_field() }}

                            <h2>Region</h2>
                            <div class="toggle-btn-grp">
                                @foreach (config('digitalocean.regions') as $regionCode => $region)
                                    <label onclick="" class="toggle-btn btn btn-default">
                                        <input type="radio" name="region" value="{{ $regionCode }}" @if ($regionCode == 'ams2') checked="checked" @endif/>
                                        {{ $region['name'] }}
                                    </label>
                                @endforeach
                            </div>

                            <h2>Size</h2>
                            <div class="toggle-btn-grp">
                                @foreach (config('digitalocean.sizes') as $sizeSlug => $doSize)
                                    @if ($doSize['memory'] >= $requiredMemory)
                                        <label onclick="" class="toggle-btn btn btn-default">
                                            <input type="radio" name="size" value="{{ $doSize['slug'] }}" @if ($doSize['slug'] == $size['default']) checked="checked" @endif/>
                                            {{ $doSize['slug'] }} / ${{ $doSize['priceMonthly'] }}
                                            @if ($size['required'] == $doSize['slug'])
                                                <hr>Required
                                            @elseif($size['suggested'] == $doSize['slug'])
                                                <hr>Suggested
                                            @endif
                                        </label>
                                    @endif
                                @endforeach
                            </div>

                            <input type="hidden" name="id" value="{{ $id }}" />
                            <input type="hidden" name="provisionid" value="{{ $provisionid }}" />
                            <input type="hidden" name="uuid" value="{{ $uuid }}" />
                            <input type="hidden" name="name" value="{{ $repo }}" />
                            <input type="hidden" name="repo" value="{{ $provision['repo'] }}" />
                            <input type="hidden" name="distro" value="{{ $distro }}" />

                            @if (count($keys) > 0)<h2>SSH Keys</h2>@endif

                            <div class="toggle-btn-grp">
                            @forelse ($keys as $keyId => $keyName)
                                    <label onclick="" class="toggle-btn btn btn-default">
                                        <input type="checkbox" name="keys[{{$keyId}}]" value="{{$keyId}}" checked="checked"/>{{$keyName}}
                                    </label>
                            @empty
                                <p>No SSH keys to add - we'll set you a root password</p>
                            @endforelse

                            </div>

                            <input type="submit" class="btn btn-default">
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
