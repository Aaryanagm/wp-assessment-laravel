<!DOCTYPE html>
<html>
<head>
    <title>WP Assessment</title>
</head>
<body>

<nav>
    <a href="/dashboard">Home</a>

    @if(!$userRole)
        <a href="/login">Login</a>
    @else
        <a href="/logout">Logout</a>
    @endif

    @if($userRole === 'gold')
        <a href="#">Gold Deals</a>
    @endif

    @if($userRole === 'silver')
        <a href="#">Silver Offers</a>
    @endif
</nav>

<hr>

@yield('content')

</body>
</html>