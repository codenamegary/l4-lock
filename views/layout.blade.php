<!DOCTYPE html>
<html>
<head>
    <title>{{ $view['title'] }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css">
</head>
<body>
@yield('top')
<div class="container">
@yield('content')
</div>
@yield('footer')    
<script src="//netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>
</body>
</html>
