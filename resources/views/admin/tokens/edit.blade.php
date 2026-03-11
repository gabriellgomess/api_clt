@extends('admin.layout')

@section('title', 'Editar Token')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="d-flex align-items-center gap-2 mb-4">
            <a href="{{ route('admin.tokens.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div>
                <h4 class="mb-0 fw-semibold">Editar Token</h4>
                <small class="text-muted font-monospace">{{ $clientToken->alias }}</small>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-4">
                <form method="POST" action="{{ route('admin.tokens.update', $clientToken) }}">
                    @csrf @method('PUT')

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Alias</label>
                        <input type="text" class="form-control font-monospace bg-light"
                               value="{{ $clientToken->alias }}" disabled>
                        <div class="form-text">O alias não pode ser alterado.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Descrição</label>
                        <input type="text" name="descricao"
                               class="form-control @error('descricao') is-invalid @enderror"
                               value="{{ old('descricao', $clientToken->descricao) }}"
                               placeholder="ex: Sistema de RH da empresa X">
                        @error('descricao')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="ativo" value="1"
                                   id="ativo" {{ $clientToken->ativo ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold" for="ativo">Token ativo</label>
                        </div>
                        <div class="form-text">Tokens inativos são rejeitados imediatamente.</div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-dark">
                            <i class="bi bi-check-lg me-1"></i>Salvar
                        </button>
                        <a href="{{ route('admin.tokens.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
