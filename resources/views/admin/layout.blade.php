<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin') — Dataprev</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .navbar-brand { font-weight: 600; letter-spacing: .5px; }
        .token-code { font-family: monospace; font-size: .85rem; word-break: break-all; }
        .badge-ativo { background-color: #198754; }
        .badge-inativo { background-color: #6c757d; }
        .table th { font-size: .8rem; text-transform: uppercase; letter-spacing: .5px; color: #6c757d; }
    </style>
</head>
<body>

<nav class="navbar navbar-dark bg-dark mb-4">
    <div class="container">
        <div class="d-flex align-items-center gap-3">
            <span class="navbar-brand mb-0">
                <i class="bi bi-shield-lock me-2"></i>Dataprev Admin
            </span>
            <a href="{{ route('admin.dashboard') }}"
               class="text-white-50 small text-decoration-none {{ request()->routeIs('admin.dashboard') ? 'text-white fw-semibold' : '' }}">
                <i class="bi bi-speedometer2 me-1"></i>Dashboard
            </a>
            <a href="{{ route('admin.tokens.index') }}"
               class="text-white-50 small text-decoration-none {{ request()->routeIs('admin.tokens.*') ? 'text-white fw-semibold' : '' }}">
                <i class="bi bi-key me-1"></i>Tokens
            </a>
        </div>
        <div class="d-flex align-items-center gap-3">
            <span id="relogio" class="text-white-50 small font-monospace"></span>
            <span class="text-white-50 small">{{ Auth::user()->name }}</span>
            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-light">
                    <i class="bi bi-box-arrow-right me-1"></i>Sair
                </button>
            </form>
        </div>
    </div>
</nav>

<div class="container pb-5">
    @yield('content')
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

{{-- Toast de confirmação de cópia --}}
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999">
    <div id="copyToast" class="toast align-items-center text-bg-success border-0" role="alert">
        <div class="d-flex">
            <div class="toast-body">
                <i class="bi bi-check-circle me-2"></i>Token copiado para a área de transferência.
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

<script>
(function tick() {
    const now = new Date();
    const d = now.toLocaleDateString('pt-BR', { day: '2-digit', month: '2-digit', year: 'numeric' });
    const t = now.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
    document.getElementById('relogio').textContent = d + '  ' + t;
    setTimeout(tick, 1000);
})();

function copyToClipboard(text) {
    const done = () => {
        const toast = new bootstrap.Toast(document.getElementById('copyToast'), { delay: 2500 });
        toast.show();
    };

    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(text).then(done);
    } else {
        // Fallback para HTTP
        const el = document.createElement('textarea');
        el.value = text;
        el.style.cssText = 'position:fixed;opacity:0';
        document.body.appendChild(el);
        el.select();
        document.execCommand('copy');
        document.body.removeChild(el);
        done();
    }
}
</script>
@yield('scripts')
</body>
</html>
