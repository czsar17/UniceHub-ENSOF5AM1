<?php

namespace App\Http\Controllers;

use App\Models\Atividade;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

// configuracoes da conta
class ConfigController extends Controller
{
    public function config()
    {
        return view('config');
    }

    public function trocarSenha(Request $request)
    {
        $request->validate([
            'current_password'      => 'required',
            'password'              => 'required|min:8|confirmed',
        ], [
            'current_password.required' => 'Informe a senha atual.',
            'password.min'              => 'A nova senha deve ter no mínimo 8 caracteres.',
            'password.confirmed'        => 'As senhas não coincidem.',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'errors' => ['current_password' => ['Senha atual incorreta.']]
            ], 422);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        Atividade::create([
            'user_id'   => $user->id,
            'descricao' => 'Alterou a senha da conta.',
        ]);

        return response()->json(['message' => 'Senha alterada com sucesso.']);
    }

    public function excluirConta(Request $request)
    {
        $request->validate([
            'password' => 'required',
        ], [
            'password.required' => 'Informe sua senha para excluir a conta.',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'errors' => ['password' => ['Senha incorreta.']]
            ], 422);
        }

        if ($user->is_admin && !User::where('is_admin', true)->where('id', '!=', $user->id)->exists()) {
            return response()->json([
                'errors' => ['password' => ['Defina outro administrador antes de excluir esta conta.']]
            ], 422);
        }

        DB::transaction(function () use ($user) {
            $user->delete();
        });

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'message' => 'Conta excluida com sucesso.',
            'redirect' => route('login'),
        ]);
    }
}
