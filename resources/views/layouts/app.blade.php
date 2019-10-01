<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- CSRF Token -->
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name') }}</title>


        <!-- Styles -->
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
</head>
<body>
@section('sidebar')
@show

<div class="container" style="max-width: 1200px; margin: auto">
    <h1>Hywel's Pokemon Directory</h1>
    <nav>
        <div class="row">
            <table class="table">
                <tr>
                    @foreach($dictionary as $letter)
                        <td style="padding: 5px">
                            <a href="/{{$letter}}"><strong>{{$letter}}</strong></a>
                        </td>
                    @endforeach
                </tr>
            </table>
        </div>
        <div class="row">
            <form class="form-inline" method="GET" action="/" >
                <div class="form-group">
                        <input type="search" class="form-control" name="searchString" id="searchString" placeholder="Type a Pokemon name, a few characters from a Pokemon name or the first letter of a Pokemon name" size="100">
                </div>
                <button type="submit" class="btn btn-default">Search</button>
            </form>
        </div>
    </nav>
    @yield('content')
</div>
</body>
</html>
