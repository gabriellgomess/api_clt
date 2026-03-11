@extends('admin.layout')

@section('title', 'Tokens de Acesso')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0 fw-semibold">Tokens de Acesso</h4>
        <small class="text-muted">Clientes autorizados a consumir a API Dataprev</small>
    </div>
    <a href="{{ route('admin.tokens.create') }}" class="btn btn-dark">
        <i class="bi bi-plus-lg me-1"></i>Novo Token
    </a>
</div>

{{-- Alerta de token recém-criado ou regenerado --}}
@if (session('new_token'))
    <div class="alert alert-success alert-dismissible shadow-sm" role="alert">
        <div class="fw-semibold mb-1">
            <i class="bi bi-key-fill me-1"></i>
            Token <strong>{{ session('new_alias') }}</strong> gerado com sucesso:
        </div>
        <div class="d-flex align-items-center gap-2 mt-2">
            <code class="token-code flex-grow-1 bg-white border rounded px-3 py-2 d-block">{{ session('new_token') }}</code>
            <button class="btn btn-sm btn-outline-secondary flex-shrink-0"
                    onclick="copyToClipboard('{{ session('new_token') }}')" title="Copiar">
                <i class="bi bi-clipboard"></i>
            </button>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- Mensagens de sucesso --}}
@if (session('success'))
    <div class="alert alert-success alert-dismissible shadow-sm py-2" role="alert">
        <i class="bi bi-check-circle me-1"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="card shadow-sm">
    <div class="card-body p-0">
        @if ($tokens->isEmpty())
            <div class="text-center text-muted py-5">
                <i class="bi bi-key fs-1 d-block mb-2 opacity-25"></i>
                Nenhum token cadastrado.
                <a href="{{ route('admin.tokens.create') }}">Crie o primeiro.</a>
            </div>
        @else
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Alias</th>
                        <th>Descrição</th>
                        <th>Token</th>
                        <th>Status</th>
                        <th>Criado em</th>
                        <th class="text-end pe-4">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($tokens as $clientToken)
                        <tr>
                            <td class="ps-4">
                                <span class="fw-semibold font-monospace">{{ $clientToken->alias }}</span>
                            </td>
                            <td class="text-muted small">{{ $clientToken->descricao ?: '—' }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-1">
                                    <code class="token-code text-muted small">
                                        {{ substr($clientToken->token, 0, 16) }}...
                                    </code>
                                    <button class="btn btn-sm btn-outline-secondary py-0 px-1"
                                            onclick="copyToClipboard('{{ $clientToken->token }}')" title="Copiar token completo">
                                        <i class="bi bi-clipboard" style="font-size:.75rem"></i>
                                    </button>
                                </div>
                            </td>
                            <td>
                                @if ($clientToken->ativo)
                                    <span class="badge badge-ativo">Ativo</span>
                                @else
                                    <span class="badge badge-inativo">Inativo</span>
                                @endif
                            </td>
                            <td class="text-muted small">{{ $clientToken->created_at->format('d/m/Y H:i') }}</td>
                            <td class="text-end pe-4">
                                <div class="d-flex justify-content-end gap-1">
                                    {{-- Editar --}}
                                    <a href="{{ route('admin.tokens.edit', $clientToken) }}"
                                       class="btn btn-sm btn-outline-secondary" title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </a>

                                    {{-- Ativar/Desativar --}}
                                    <form method="POST" action="{{ route('admin.tokens.toggle', $clientToken) }}">
                                        @csrf @method('PATCH')
                                        <button type="submit"
                                                class="btn btn-sm {{ $clientToken->ativo ? 'btn-outline-warning' : 'btn-outline-success' }}"
                                                title="{{ $clientToken->ativo ? 'Desativar' : 'Ativar' }}">
                                            <i class="bi bi-{{ $clientToken->ativo ? 'pause-circle' : 'play-circle' }}"></i>
                                        </button>
                                    </form>

                                    {{-- Regenerar token --}}
                                    <form method="POST" action="{{ route('admin.tokens.regenerate', $clientToken) }}"
                                          onsubmit="return confirm('Regenerar o token de {{ $clientToken->alias }}? O token atual deixará de funcionar imediatamente.')">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-primary" title="Regenerar token">
                                            <i class="bi bi-arrow-clockwise"></i>
                                        </button>
                                    </form>

                                    {{-- Excluir --}}
                                    <form method="POST" action="{{ route('admin.tokens.destroy', $clientToken) }}"
                                          onsubmit="return confirm('Excluir o token de {{ $clientToken->alias }}? Esta ação não pode ser desfeita.')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Excluir">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>
@endsection
