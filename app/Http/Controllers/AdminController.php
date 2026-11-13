<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

// area do admin: usuarios e aprovacao de professores (rotas com middleware admin)
class AdminController extends Controller
{
    public function usuarios()
    {
        return response()->json([
            'usuarios' => User::orderByDesc('is_admin')
                ->orderBy('name')
                ->get(['id', 'name', 'email', 'tipo', 'curso', 'foto', 'is_admin'])
                ->map(fn (User $usuario) => [
                    'id' => $usuario->id,
                    'name' => $usuario->name,
                    'email' => $usuario->email,
                    'tipo' => $usuario->tipo,
                    'curso' => $usuario->curso,
                    'foto' => asset($usuario->foto ?: 'images/default-user.png'),
                    'is_admin' => (bool) $usuario->is_admin,
                    'is_me' => $usuario->id === Auth::id(),
                ])->values(),
        ]);
    }

    public function atualizarUsuario(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'is_admin' => 'required|boolean',
        ]);

        $usuario = User::findOrFail($data['user_id']);
        $isAdmin = (bool) $data['is_admin'];

        if (!$isAdmin && $usuario->is_admin && User::where('is_admin', true)->count() <= 1) {
            return response()->json([
                'errors' => ['is_admin' => ['O sistema precisa manter pelo menos um administrador.']],
            ], 422);
        }

        $usuario->is_admin = $isAdmin;
        $usuario->save();

        return response()->json(['message' => 'Permissão atualizada com sucesso.']);
    }

    public function professoresPendentes()
    {
        return response()->json([
            'professores' => User::where('tipo', 'professor')
                ->where('approval_status', 'pending')
                ->orderBy('approval_requested_at')
                ->get(['id', 'name', 'email', 'cpf', 'data_nascimento', 'curso', 'telefone', 'approval_requested_at'])
                ->map(fn (User $professor) => [
                    'id' => $professor->id,
                    'name' => $professor->name,
                    'email' => $professor->email,
                    'cpf' => $professor->cpf,
                    'data_nascimento' => $professor->data_nascimento ? date('d/m/Y', strtotime($professor->data_nascimento)) : 'Não informado',
                    'curso' => $professor->curso ?: 'Não informado',
                    'telefone' => $professor->telefone ?: 'Não informado',
                    'requested_at' => optional($professor->approval_requested_at)->format('d/m/Y H:i') ?: 'Data não informada',
                ])->values(),
        ]);
    }

    public function revisarProfessor(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'action' => ['required', Rule::in(['approve', 'reject'])],
        ]);

        $professor = User::where('tipo', 'professor')->findOrFail($data['user_id']);

        if ($professor->approval_status !== 'pending') {
            return response()->json([
                'errors' => ['user_id' => ['Este cadastro já foi analisado.']],
            ], 422);
        }

        $professor->approval_status = $data['action'] === 'approve' ? 'approved' : 'rejected';
        $professor->approval_reviewed_at = now();
        $professor->approval_reviewed_by = Auth::id();
        $professor->save();

        return response()->json([
            'message' => $data['action'] === 'approve'
                ? 'Cadastro de professor aprovado.'
                : 'Cadastro de professor negado.',
        ]);
    }
}
