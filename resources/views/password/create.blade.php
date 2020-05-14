<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
<h1>{{$user->name . " " . $user->surname}} </h1>
<ul>
    <li>Login : {{$user->username}}</li>
    <li>Parol : {{$password}}</li>
    <br>
    <a href="https://1of.az">One Office</a>
</ul>
</body>
</html>
