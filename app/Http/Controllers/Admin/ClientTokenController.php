<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClientToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ClientTokenController extends Controller
{
    public function index()
    {
        $tokens = ClientToken::orderBy('alias')->get();
        return view('admin.tokens.index', compact('tokens'));
    }

    public function create()
    {
        return view('admin.tokens.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'alias'     => ['required', 'string', 'max:50', 'unique:dataprev_client_tokens,alias', 'regex:/^[a-z0-9-]+$/'],
            'descricao' => 'nullable|string|max:200',
        ], [
            'alias.regex'  => 'O alias só pode conter letras minúsculas, números e hífens.',
            'alias.unique' => 'Este alias já está em uso.',
        ]);

        $data['token'] = ClientToken::generateToken();
        $data['ativo'] = true;

        $clientToken = ClientToken::create($data);

        $this->clearCache();

        return redirect()->route('admin.tokens.index')
            ->with('new_token', $clientToken->token)
            ->with('new_alias', $clientToken->alias);
    }

    public function edit(ClientToken $clientToken)
    {
        return view('admin.tokens.edit', compact('clientToken'));
    }

    public function update(Request $request, ClientToken $clientToken)
    {
        $data = $request->validate([
            'descricao' => 'nullable|string|max:200',
            'ativo'     => 'boolean',
        ]);

        $clientToken->update($data);
        $this->clearCache();

        return redirect()->route('admin.tokens.index')->with('success', 'Token atualizado com sucesso.');
    }

    public function destroy(ClientToken $clientToken)
    {
        $clientToken->delete();
        $this->clearCache();

        return redirect()->route('admin.tokens.index')->with('success', "Token \"{$clientToken->alias}\" removido.");
    }

    public function regenerate(ClientToken $clientToken)
    {
        $clientToken->update(['token' => ClientToken::generateToken()]);
        $this->clearCache();

        return redirect()->route('admin.tokens.index')
            ->with('new_token', $clientToken->token)
            ->with('new_alias', $clientToken->alias);
    }

    public function toggle(ClientToken $clientToken)
    {
        $clientToken->update(['ativo' => !$clientToken->ativo]);
        $this->clearCache();

        $status = $clientToken->ativo ? 'ativado' : 'desativado';
        return redirect()->route('admin.tokens.index')->with('success', "Token \"{$clientToken->alias}\" {$status}.");
    }

    private function clearCache(): void
    {
        Cache::forget('dataprev_client_tokens_active');
    }
}
