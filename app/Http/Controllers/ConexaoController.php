<?php

namespace App\Http\Controllers;

use App\Models\Follower;
use App\Models\Projeto;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

// conexoes entre usuarios (seguir, aceitar, recusar, bloquear)
class ConexaoController extends Controller
{
    public function conexoes()
    {
        $user = Auth::user();

        // QUEM SEGUE VOCÊ
        $seguidores = Follower::where('seguido_id', $user->id)
            ->where('status', 'aceito')
            ->with('seguidor')
            ->get();

        // QUEM VOCÊ SEGUE
        $seguindo = Follower::where('seguidor_id', $user->id)
            ->where('status', 'aceito')
            ->with('seguido')
            ->get();

        // SOLICITAÇÕES PENDENTES
        $solicitacoes = Follower::where('seguido_id', $user->id)
            ->where('status', 'pendente')
            ->with('seguidor')
            ->latest()
            ->get();

        // CONVITES PENDENTES PARA PARTICIPAR DE PROJETOS
        $solicitacoesProjeto = Projeto::whereHas('membros', function ($query) use ($user) {
            $query->where('users.id', $user->id)
                ->where('projeto_user.status', 'pendente');
        })
            ->with('criador')
            ->latest()
            ->get();

        // BLOQUEADOS
        $bloqueados = Follower::where('seguidor_id', $user->id)
            ->where('status', 'bloqueado')
            ->with('seguido')
            ->get();

        // SUGESTÕES
        $ignorados = Follower::where('seguidor_id', $user->id)
            ->pluck('seguido_id');

        $sugestoes = User::where('id', '!=', $user->id)
            ->whereNotIn('id', $ignorados)
            ->inRandomOrder()
            ->take(3)
            ->get();

        $usuariosPesquisa = User::where('id', '!=', $user->id)
            ->orderBy('name')
            ->get();

        $projetosPesquisa = Projeto::with(['criador', 'membros'])
            ->latest()
            ->get();

        $relacoesUsuario = Follower::where(function ($query) use ($user) {
            $query->where('seguidor_id', $user->id)
                ->orWhere('seguido_id', $user->id);
        })->get();

        $statusUsuario = function (User $usuario) use ($relacoesUsuario, $user) {
            $enviada = $relacoesUsuario->first(fn ($relacao) => (int) $relacao->seguidor_id === (int) $user->id && (int) $relacao->seguido_id === (int) $usuario->id);
            $recebida = $relacoesUsuario->first(fn ($relacao) => (int) $relacao->seguidor_id === (int) $usuario->id && (int) $relacao->seguido_id === (int) $user->id);

            if (($enviada && $enviada->status === 'bloqueado') || ($recebida && $recebida->status === 'bloqueado')) {
                return ['label' => 'Bloqueado', 'can_follow' => false];
            }

            if (($enviada && $enviada->status === 'aceito') || ($recebida && $recebida->status === 'aceito')) {
                return ['label' => 'Conectado', 'can_follow' => false];
            }

            if ($enviada && $enviada->status === 'pendente') {
                return ['label' => 'Solicitação enviada', 'can_follow' => false];
            }

            if ($recebida && $recebida->status === 'pendente') {
                return ['label' => 'Solicitação recebida', 'can_follow' => false];
            }

            return ['label' => 'Conectar', 'can_follow' => true];
        };

        $usuariosPreview = $usuariosPesquisa->map(function (User $usuario) use ($statusUsuario) {
            $status = $statusUsuario($usuario);

            return [
                'id' => $usuario->id,
                'type' => 'usuario',
                'name' => $usuario->name,
                'course' => $usuario->curso ?: 'Curso não informado',
                'role' => ucfirst($usuario->tipo ?? 'usuário'),
                'photo' => asset($usuario->foto ?: 'images/default-user.png'),
                'about' => $usuario->sobre_mim ?: 'Este usuário ainda não adicionou uma descrição.',
                'email' => $usuario->email,
                'technologies' => $usuario->tecnologias ?? [],
                'search' => trim($usuario->name . ' ' . $usuario->curso . ' ' . $usuario->tipo . ' ' . implode(' ', $usuario->tecnologias ?? [])),
                'follow_url' => route('seguir.enviar', $usuario->id),
                'follow_label' => $status['label'],
                'can_follow' => $status['can_follow'],
            ];
        })->values();

        $projetosPreview = $projetosPesquisa->map(function (Projeto $projeto) {
            return [
                'id' => $projeto->id,
                'type' => 'projeto',
                'name' => $projeto->nome,
                'course' => $projeto->categoria ?: 'Projeto',
                'role' => $projeto->status,
                'photo' => $projeto->capa ? asset($projeto->capa) : asset('images/loading.png'),
                'about' => strip_tags($projeto->descricao ?: 'Este projeto ainda não possui descrição.'),
                'creator' => $projeto->criador->name ?? 'Criador não informado',
                'technologies' => $projeto->tecnologias ?? [],
                'members_count' => $projeto->membros->count(),
                'created_at' => optional($projeto->created_at)->format('d/m/Y'),
                'search' => trim($projeto->nome . ' ' . $projeto->categoria . ' ' . $projeto->status . ' ' . implode(' ', $projeto->tecnologias ?? [])),
                'view_url' => route('projetos'),
            ];
        })->values();

        return view('conexoes', compact(
            'seguidores',
            'seguindo',
            'solicitacoes',
            'solicitacoesProjeto',
            'bloqueados',
            'sugestoes',
            'usuariosPesquisa',
            'projetosPesquisa',
            'relacoesUsuario',
            'usuariosPreview',
            'projetosPreview'
        ));
    }

    public function seguir($id)
    {
        $user = Auth::user();

        if($user->id == $id){
            return back();
        }

        $existe = Follower::where('seguidor_id', $user->id)
            ->where('seguido_id', $id)
            ->exists();

        if(!$existe){

            Follower::create([
                'seguidor_id' => $user->id,
                'seguido_id' => $id,
                'status' => 'pendente'
            ]);
        }

        return back();
    }

    public function aceitarSeguidor($id)
    {
        $follow = Follower::findOrFail($id);

        if($follow->seguido_id != Auth::id()){
            abort(403);
        }

        $follow->status = 'aceito';

        $follow->save();

        return back();
    }

    public function recusarSeguidor($id)
    {
        $follow = Follower::findOrFail($id);

        if($follow->seguido_id != Auth::id()){
            abort(403);
        }

        $follow->delete();

        return back();
    }

    public function bloquear($id)
    {
        $follow = Follower::where(function($q) use ($id){

            $q->where('seguidor_id', Auth::id())
              ->where('seguido_id', $id);

        })->orWhere(function($q) use ($id){

            $q->where('seguidor_id', $id)
              ->where('seguido_id', Auth::id());

        })->first();

        if($follow){

            $follow->status = 'bloqueado';
            $follow->save();

        }

        return back();
    }
}
