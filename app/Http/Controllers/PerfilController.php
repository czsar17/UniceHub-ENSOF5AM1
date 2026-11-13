<?php

namespace App\Http\Controllers;

use App\Models\Atividade;
use App\Models\Follower;
use App\Models\Projeto;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

// perfil do usuario logado e de outros usuarios
class PerfilController extends Controller
{
    public function perfil()
    {
        $perfilUser = Auth::user();

        $atividades = $perfilUser
            ->atividades()
            ->latest()
            ->take(5)
            ->get();

        $projetosPerfil = $this->projetosDoUsuario($perfilUser);
        $perfilFollowStatus = null;

        return view('perfil', compact('atividades', 'perfilUser', 'projetosPerfil', 'perfilFollowStatus'));
    }

    public function visualizarUsuario(User $user)
    {
        if ($user->id === Auth::id()) {
            return redirect()->route('perfil');
        }

        $perfilUser = $user;

        $atividades = $perfilUser
            ->atividades()
            ->latest()
            ->take(5)
            ->get();

        $projetosPerfil = $this->projetosDoUsuario($perfilUser);
        $perfilFollowStatus = Follower::where('seguidor_id', Auth::id())
            ->where('seguido_id', $perfilUser->id)
            ->value('status');

        return view('perfil', compact('atividades', 'perfilUser', 'projetosPerfil', 'perfilFollowStatus'));
    }

    public function atualizarPerfil(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('users')->ignore($user->id),
            ],
            'telefone' => 'nullable|max:20',
            'data_nascimento' => 'nullable|date',
            'curso' => [
                'nullable',
                Rule::in([
                    'ADS',
                    'Análise e Desenvolvimento de Sistemas',
                    'Engenharia de Software',
                    'Ciência da Computação',
                ]),
            ],
            'sobre_mim' => 'nullable|max:350',
            'interesses_markdown' => 'nullable|string',
            'tecnologias' => 'nullable|array|max:8',
            'tecnologias.*' => 'nullable|string|max:30',
        ]);

        $tecnologias = collect($request->input('tecnologias', []))
            ->map(fn ($tecnologia) => trim($tecnologia))
            ->filter()
            ->unique()
            ->take(8)
            ->values()
            ->all();

        $dadosPerfil = [
            'name' => $request->name,
            'email' => $request->email,
            'telefone' => $request->telefone,
            'curso' => $request->curso,
            'sobre_mim' => $request->sobre_mim,
            'interesses_markdown' => $request->interesses_markdown,
            'tecnologias' => $tecnologias,
        ];

        if ($request->has('data_nascimento')) {
            $dadosPerfil['data_nascimento'] = $request->data_nascimento;
        }

        $user->fill($dadosPerfil);

        if($request->hasFile('foto')){
            $arquivo = $request->file('foto');
            $nomeArquivo = time().'.'.$arquivo->getClientOriginalExtension();
            $arquivo->move(public_path('images/perfis'), $nomeArquivo);
            $user->foto = 'images/perfis/'.$nomeArquivo;
        }

        if ($user->isDirty() || $request->hasFile('foto')) {
            $user->save();

            Atividade::create([
                'user_id' => $user->id,
                'descricao' => 'Atualizou as informações do perfil'
            ]);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Perfil atualizado com sucesso.',
                'user' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'telefone' => $user->telefone,
                    'curso' => $user->curso,
                    'data_nascimento' => $user->data_nascimento,
                ],
            ]);
        }

        return back();
    }

    // projetos que a pessoa criou ou participa
    private function projetosDoUsuario(User $user)
{
    return Projeto::where(function ($query) use ($user) {
        $query->where('user_id', $user->id)
            ->orWhereHas('membros', function ($query) use ($user) {
                $query->where('users.id', $user->id)
                    ->where('projeto_user.status', 'aceito');
            });
    })
        ->with(['criador', 'membros' => function ($query) {
            $query->wherePivot('status', 'aceito');
        }])
        ->latest()
        ->get();
}
}
