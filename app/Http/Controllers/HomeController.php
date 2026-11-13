<?php

namespace App\Http\Controllers;

use App\Models\ComentarioProjeto;
use App\Models\Follower;
use App\Models\Projeto;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

// feed da home, busca e notificacoes do header
class HomeController extends Controller
{
    public function home()
    {
        $user = Auth::user();

        $usuariosIgnorados = Follower::where('seguidor_id', $user->id)
            ->pluck('seguido_id');

        $sugestoes = User::where('id', '!=', $user->id)
            ->whereNotIn('id', $usuariosIgnorados)
            ->inRandomOrder()
            ->take(2)
            ->get();

        $projetosFeed = Projeto::with(['criador', 'curtidas'])
            ->withCount('curtidas')
            ->withCount('comentarios')
            ->latest()
            ->get();

        $projetosDestaque = Projeto::with(['membros' => function ($query) {
                $query->wherePivot('status', 'aceito');
            }])
            ->withCount('curtidas')
            ->withCount('comentarios')
            ->get()
            ->sortByDesc(fn ($projeto) => $projeto->curtidas_count + $projeto->comentarios_count)
            ->take(2)
            ->values();

        $tecnologiasEmAlta = User::query()
            ->whereNotNull('tecnologias')
            ->get(['tecnologias'])
            ->flatMap(fn ($usuario) => $usuario->tecnologias ?? [])
            ->map(fn ($tecnologia) => trim((string) $tecnologia))
            ->filter()
            ->groupBy(fn ($tecnologia) => strtolower($tecnologia))
            ->map(fn ($grupo) => [
                'nome' => $grupo->first(),
                'total' => $grupo->count(),
            ])
            ->sortByDesc('total')
            ->take(4)
            ->values();

        return view('home', compact(
            'sugestoes',
            'projetosFeed',
            'projetosDestaque',
            'tecnologiasEmAlta'
        ));
    }

    public function buscar(Request $request)
    {
        $termo = trim((string) $request->query('q', ''));

        $usuarios = collect();
        $projetos = collect();

        if ($termo !== '') {
            $usuarios = User::where('id', '!=', Auth::id())
                ->where(function ($query) use ($termo) {
                    $query->where('name', 'like', "%{$termo}%")
                        ->orWhere('curso', 'like', "%{$termo}%")
                        ->orWhere('tipo', 'like', "%{$termo}%");
                })
                ->orderBy('name')
                ->get();

            $projetos = Projeto::with(['criador', 'membros'])
                ->where(function ($query) use ($termo) {
                    $query->where('nome', 'like', "%{$termo}%")
                        ->orWhere('categoria', 'like', "%{$termo}%")
                        ->orWhere('status', 'like', "%{$termo}%")
                        ->orWhere('descricao', 'like', "%{$termo}%");
                })
                ->latest()
                ->get();
        }

        return view('busca', compact('termo', 'usuarios', 'projetos'));
    }

    public function notificacoesHeader()
    {
        $user = Auth::user();

        $solicitacoesConexao = Follower::where('seguido_id', $user->id)
            ->where('status', 'pendente')
            ->with('seguidor')
            ->latest()
            ->take(4)
            ->get()
            ->map(fn ($follow) => [
                'tipo' => 'Conexão',
                'icone' => 'fa-user-plus',
                'titulo' => optional($follow->seguidor)->name . ' quer se conectar',
                'texto' => optional($follow->seguidor)->curso ?: 'Nova solicitação de conexão.',
                'url' => route('conexoes'),
                'data' => optional($follow->created_at)->diffForHumans(),
                'timestamp' => optional($follow->created_at)->timestamp ?? 0,
            ]);

        $conexoesAceitas = Follower::where('seguidor_id', $user->id)
            ->where('status', 'aceito')
            ->with('seguido')
            ->latest('updated_at')
            ->take(3)
            ->get()
            ->map(fn ($follow) => [
                'tipo' => 'Conexão',
                'icone' => 'fa-user-check',
                'titulo' => optional($follow->seguido)->name . ' aceitou sua conexão',
                'texto' => 'Vocês agora estão conectados.',
                'url' => route('conexoes'),
                'data' => optional($follow->updated_at)->diffForHumans(),
                'timestamp' => optional($follow->updated_at)->timestamp ?? 0,
            ]);

        $convitesProjeto = Projeto::whereHas('membros', function ($query) use ($user) {
                $query->where('users.id', $user->id)
                    ->where('projeto_user.status', 'pendente');
            })
            ->with('criador')
            ->latest()
            ->take(4)
            ->get()
            ->map(fn ($projeto) => [
                'tipo' => 'Projeto',
                'icone' => 'fa-folder-open',
                'titulo' => 'Convite para ' . $projeto->nome,
                'texto' => 'Enviado por ' . (optional($projeto->criador)->name ?: 'um usuário'),
                'url' => route('projetos'),
                'data' => optional($projeto->updated_at)->diffForHumans(),
                'timestamp' => optional($projeto->updated_at)->timestamp ?? 0,
            ]);

        $comentarios = ComentarioProjeto::whereHas('projeto', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->where('user_id', '!=', $user->id)
            ->with(['user', 'projeto'])
            ->latest()
            ->take(5)
            ->get()
            ->map(fn ($comentario) => [
                'tipo' => 'Comentário',
                'icone' => 'fa-comment',
                'titulo' => optional($comentario->user)->name . ' comentou em ' . optional($comentario->projeto)->nome,
                'texto' => Str::limit($comentario->comentario, 80),
                'url' => $comentario->projeto ? route('projetos.show', $comentario->projeto) : route('home'),
                'data' => optional($comentario->created_at)->diffForHumans(),
                'timestamp' => optional($comentario->created_at)->timestamp ?? 0,
            ]);

        $curtidas = DB::table('projeto_curtidas')
            ->join('projetos', 'projetos.id', '=', 'projeto_curtidas.projeto_id')
            ->join('users', 'users.id', '=', 'projeto_curtidas.user_id')
            ->where('projetos.user_id', $user->id)
            ->where('projeto_curtidas.user_id', '!=', $user->id)
            ->select(
                'projeto_curtidas.created_at',
                'projetos.id as projeto_id',
                'projetos.nome as projeto_nome',
                'users.name as user_name'
            )
            ->latest('projeto_curtidas.created_at')
            ->take(5)
            ->get()
            ->map(fn ($curtida) => [
                'tipo' => 'Interação',
                'icone' => 'fa-heart',
                'titulo' => $curtida->user_name . ' curtiu ' . $curtida->projeto_nome,
                'texto' => 'Seu projeto recebeu uma nova curtida.',
                'url' => route('projetos.show', $curtida->projeto_id),
                'data' => Carbon::parse($curtida->created_at)->diffForHumans(),
                'timestamp' => Carbon::parse($curtida->created_at)->timestamp,
            ]);

        $notificacoes = collect()
            ->merge($solicitacoesConexao)
            ->merge($conexoesAceitas)
            ->merge($convitesProjeto)
            ->merge($comentarios)
            ->merge($curtidas)
            ->filter(fn ($notificacao) => trim((string) $notificacao['titulo']) !== '')
            ->sortByDesc(fn ($notificacao) => $notificacao['timestamp'])
            ->take(10)
            ->map(function ($notificacao) {
                unset($notificacao['timestamp']);
                return $notificacao;
            })
            ->values();

        return response()->json([
            'total' => $notificacoes->count(),
            'notificacoes' => $notificacoes,
        ]);
    }
}
