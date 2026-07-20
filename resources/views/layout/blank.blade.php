<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NOTESSYTEM</title>
    @vite(['resources/css/style.css', 'resources/js/app.js'])
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#FF6D00">
    <link rel="apple-touch-icon" href="/icons/icon-192x192.png">
    <link rel="icon" type="image/png" href="/icons/icon-192x192.png">
    <script>
        // Registra os recursos PWA quando o navegador oferece suporte.
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/sw.js');
        }
    </script>
    <style>
        .page-transition { animation: pageEnter .5s cubic-bezier(.34,1.56,.64,1) both; }
        @keyframes pageEnter {
            from { opacity: 0; transform: translateY(24px) scale(0.97); }
            to   { opacity: 1; transform: translateY(0) scale(1); }
        }
    </style>
</head>
<body>

<!-- Estrutura minima usada por paginas que nao exibem a navegacao principal. -->
<div class="blank-shell">
    <div class="container page-transition">
        @yield('content')
    </div>
</div>

@yield('modals')
</body>
</html>