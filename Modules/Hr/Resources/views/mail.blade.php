<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
    <div>
        <h1>Hormetli {{ $name }} {{ $surname }}</h1>
        <p>
            Deveti Qebul etmek ucun 
            <span style="color: blue"><a href="{{ url('https://1of.az/') }}">Daxil Olun</a> </span>
        </p>
        <p>Istifadeci Adi: {{ $username }}</p>
        <p>Sifre: {{ $password }}</p>
    </div>
</body>
</html>