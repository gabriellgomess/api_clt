@extends('admin.layout')

@section('title', 'Novo Token')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="d-flex align-items-center gap-2 mb-4">
            <a href="{{ route('admin.tokens.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div>
                <h4 class="mb-0 fw-semibold">Novo Token</h4>
                <small class="text-muted">O token será gerado automaticamente</small>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-4">
                <form method="POST" action="{{ route('admin.tokens.store') }}">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Alias <span class="text-danger">*</span></label>
                        <input type="text" name="alias"
                               class="form-control font-monospace @error('alias') is-invalid @enderror"
                               value="{{ old('alias') }}"
                               placeholder="ex: sistema-a, app-mobile, erp-cliente"
                               required>
                        <div class="form-text">Apenas letras minúsculas, números e hífens.</div>
                        @error('alias')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Descrição</label>
                        <input type="text" name="descricao"
                               class="form-control @error('descricao') is-invalid @enderror"
                               value="{{ old('descricao') }}"
                               placeholder="ex: Sistema de RH da empresa X">
                        @error('descricao')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-dark">
                            <i class="bi bi-key me-1"></i>Gerar Token
                        </button>
                        <a href="{{ route('admin.tokens.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
