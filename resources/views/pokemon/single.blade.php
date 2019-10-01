<!-- Stored in resources/views/child.blade.php -->

@extends('layouts.app')

@section('title', 'Page Title')

@section('sidebar')
    @parent


@endsection

@section('content')
    @if (!is_array($pokedetails))
        <p class="bg-warning">{{$pokedetails}}</p>
    @else
        <div class="row" style="padding-top: 20px">
            <div class="col-md-2">
                <p><strong>Name</strong></p>
            </div>
            <div class="col-md-4">
                <p>{{$pokedetails['name']}}</p>
            </div>
            <div class="col-md-2">
                <p><strong>Species</strong></p>
            </div>
            <div class="col-md-4">
                <p>{{$pokedetails['species']}}</p>
            </div>
        </div>
        <div class="row" >
            <div class="col-md-2"><p><strong>Images</strong></p></div>
            <div class="col-md-4"><img src="{{$pokedetails['frontView']}}" class="img-responsive"></div>
            <div class="col-md-4"><img src="{{$pokedetails['backView']}}" class="img-responsive"></div>
        </div>
        <div class="row">
            <div class="col-md-2">
                <p><strong>Height</strong></p>
            </div>
            <div class="col-md-1">
                <p>{{$pokedetails['height']}}</p>
            </div>
            <div class="col-md-2">
                <p><strong>Weight</strong></p>
            </div>
            <div class="col-md-1">
                <p>{{$pokedetails['weight']}}</p>
            </div>
            <div class="col-md-2">
                <p><strong>Abilities</strong></p>
            </div>
            <div class="col-md-4">
                <p>
                    @foreach ($pokedetails['abilities'] as $ability)
                        {{$ability}}<br>
                    @endforeach
                </p>
            </div>
        </div>

    @endif
@endsection
