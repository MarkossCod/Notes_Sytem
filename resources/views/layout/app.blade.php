<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>NOTESSYTEM</title>
    @vite(['resources/css/style.css', 'resources/js/app.js'])
</head>
<body>

<nav class="navbar">
    <div class="logo">NOTESSYTEM</div>
    <ul>
        <li>
            <a href="{{ route('notes.index') }}">
                <span>📋</span> Gerenciar Notas
            </a>
        </li>
        <li>
            <a href="{{ route('notes.create') }}">
                <span>➕</span> Nova Nota
            </a>
        </li>
    </ul>
</nav>

<div class="container">
    @yield('content')
</div>

</body>
</html>