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
<h1>Hörmətli {{$user->name . " " . $user->surname}}. </h1>
<ul>
    <li>İstifadəçi adı : {{$user->username}}</li>
    <li>Şifrə : {{$password}}</li>
</ul>
<br>
<p>Dəvəti qəbul etmək üçün <a href="https://1of.az">One Office</a></p>
</body>
</html>
