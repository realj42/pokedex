
@extends('layouts.app')

@section('title', 'Page Title')

@section('sidebar')
    @parent

@endsection

@section('content')
        @if (!is_array($result))
            <p class="bg-warning">{{$result}}</p>
        @else
            <div class="row" style="padding-top: 20px">
                <table class="table table-striped table-bordered">
                    @php
                        $ii = 0;
                    @endphp
                    @foreach($result as $name)
                            @if ($ii % 4 == 0)
                                <tr>
                            @endif
                            <td>
                                <a href="/pokemon/{{$name->ID}}/"><strong>{{$name->name}}</strong></a>
                            </td>
                            @php
                                $ii++;
                            @endphp
                            @if (($ii) % 4 == 0)
                                </tr>
                            @endif
                    @endforeach
                </table>
            </div>
        @endif
@endsection
