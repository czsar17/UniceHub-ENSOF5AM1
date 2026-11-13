<?php

namespace App\Http\Controllers;

use App\Models\Projeto;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ComentarioProjeto;
use App\Models\Atividade;

class ProjetoController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $projetos = Projeto::where(function ($query) use ($user) {
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

        $convites = Projeto::whereHas('membros', function ($query) use ($user) {
            $query->where('users.id', $user->id)
                ->where('projeto_user.status', 'pendente');
        })
            ->with('criador')
            ->get();

        $resumo = [
            'total' => $projetos->count(),
            'em_andamento' => $projetos->where('status', 'Em desenvolvimento')->count(),
            'concluidos' => $projetos->where('status', 'Concluído')->count(),
            'arquivados' => $projetos->where('status', 'Arquivado')->count(),
        ];

        return view('projetos.index', compact('projetos', 'convites', 'resumo'));
    }

    public function create()
    {
        $user = Auth::user();

        $seguindo = $user->seguindo()->wherePivot('status', 'aceito')->pluck('users.id');
        $seguidores = $user->seguidores()->wherePivot('status', 'aceito')->pluck('users.id');

        $ids = $seguindo->merge($seguidores)->unique();

        $pessoasDisponiveis = User::whereIn('id', $ids)
            ->where('id', '!=', $user->id)
            ->get();

        return view('projetos.form', compact('pessoasDisponiveis'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:100',
            'descricao' => 'required|string|max:5000',
            'categoria' => 'required|string',
            'status' => 'required|string',
            'tecnologias' => 'nullable|array',
            'tecnologias.*' => 'nullable|string|max:30',
            'repo_url' => 'nullable|url',
            'capa' => 'nullable|image|max:5120',
            'membros' => 'nullable|array',
            'membros.*' => 'exists:users,id',
        ]);

        $rawTecnologias = $request->input('tecnologias', []);

        if (is_string($rawTecnologias)) {
            $decodedTecnologias = json_decode($rawTecnologias, true);
            if (is_array($decodedTecnologias)) {
                $rawTecnologias = $decodedTecnologias;
            } else {
                $rawTecnologias = array_filter(array_map('trim', preg_split('/[,;]+/', $rawTecnologias) ?: []));
            }
        }

        if (!is_array($rawTecnologias)) {
            $rawTecnologias = [];
        }

        $tecnologias = collect($rawTecnologias)
            ->map(fn ($tecnologia) => is_scalar($tecnologia) ? trim((string) $tecnologia) : '')
            ->filter()
            ->unique()
            ->values()
            ->all();

        $dadosProjeto = [
            'user_id' => Auth::id(),
            'nome' => $request->nome,
            'descricao' => $request->descricao,
            'categoria' => $request->categoria,
            'status' => $request->status,
            'tecnologias' => $tecnologias,
        ];

        if ($request->filled('repo_url')) {
            $dadosProjeto['repo_url'] = $request->repo_url;
        }

        $projeto = Projeto::create($dadosProjeto);

        if ($request->hasFile('capa')) {
            $arquivo = $request->file('capa');
            $nome = time() . '.' . $arquivo->getClientOriginalExtension();
            $arquivo->move(public_path('images/projetos'), $nome);
            $projeto->capa = 'images/projetos/' . $nome;
            $projeto->save();
        }

        $projeto->membros()->syncWithoutDetaching([
            Auth::id() => ['status' => 'aceito']
        ]);

        foreach (collect($request->membros ?? []) as $membroId) {
            $projeto->membros()->syncWithoutDetaching([
                (int) $membroId => ['status' => 'pendente']
            ]);
        }

        return redirect()->route('projetos');
    }


    public function show(Projeto $projeto)
    {
        $projeto->load(['criador', 'membros' => function ($query) {
            $query->wherePivot('status', 'aceito');
        }]);

        $projetosRelacionados = Projeto::where('id', '!=', $projeto->id)
            ->where(function ($query) use ($projeto) {
                $query->where('categoria', $projeto->categoria)
                    ->orWhere('user_id', $projeto->user_id);
            })
            ->with(['criador', 'membros'])
            ->latest()
            ->take(4)
            ->get();

        return view('projetos.show', compact('projeto', 'projetosRelacionados'));
    }

    public function edit(Projeto $projeto)
    {
        $user = Auth::user();

        $podeEditar = $projeto->user_id === $user->id
            || $projeto->membros()->where('users.id', $user->id)->wherePivot('status', 'aceito')->exists();

        if (! $podeEditar) {
            abort(403);
        }

        $pessoasDisponiveis = User::where('id', '!=', $user->id)->get();

        return view('projetos.form', compact('projeto', 'pessoasDisponiveis'));
    }

   

    public function update(Request $request, Projeto $projeto)
    {
        $user = Auth::user();

        $podeEditar = $projeto->user_id === $user->id
            || $projeto->membros()->where('users.id', $user->id)->wherePivot('status', 'aceito')->exists();

        if (! $podeEditar) {
            abort(403);
        }

        $request->validate([
            'nome'          => 'required|string|max:100',
            'descricao'     => 'required|string|max:5000',
            'categoria'     => 'required|string',
            'status'        => 'required|string',
            'tecnologias'   => 'nullable|array',
            'tecnologias.*' => 'nullable|string|max:30',
            'repo_url'      => 'nullable|url',
            'capa'          => 'nullable|image|max:5120',
            'membros'       => 'nullable|array',
            'membros.*'     => 'exists:users,id',
        ]);

        $rawTecnologias = $request->input('tecnologias', []);

        if (is_string($rawTecnologias)) {
            $decoded = json_decode($rawTecnologias, true);
            $rawTecnologias = is_array($decoded)
                ? $decoded
                : array_filter(array_map('trim', preg_split('/[,;]+/', $rawTecnologias) ?: []));
        }

        if (! is_array($rawTecnologias)) {
            $rawTecnologias = [];
        }

        $tecnologias = collect($rawTecnologias)
            ->map(fn ($t) => is_scalar($t) ? trim((string) $t) : '')
            ->filter()
            ->unique()
            ->values()
            ->all();

        $projeto->nome        = $request->nome;
        $projeto->descricao   = $request->descricao;
        $projeto->categoria   = $request->categoria;
        $projeto->status      = $request->status;
        $projeto->tecnologias = $tecnologias;
        $projeto->repo_url    = $request->filled('repo_url') ? $request->repo_url : $projeto->repo_url;

        if ($request->hasFile('capa')) {
            $arquivo = $request->file('capa');
            $nome    = time() . '.' . $arquivo->getClientOriginalExtension();
            $arquivo->move(public_path('images/projetos'), $nome);
            $projeto->capa = 'images/projetos/' . $nome;
        }

        $projeto->save();

        Atividade::create([
            'user_id'   => Auth::id(),
            'descricao' => 'Atualizou o projeto "' . $projeto->nome . '"',
        ]);

        foreach (collect($request->membros ?? []) as $membroId) {
            $projeto->membros()->syncWithoutDetaching([
                (int) $membroId => ['status' => 'pendente'],
            ]);
        }

        return redirect()->route('projetos.show', $projeto)
            ->with('sucesso', 'Projeto atualizado com sucesso!');
    }   


    public function convidar(Request $request, Projeto $projeto)
    {
        if ($projeto->user_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'membros' => 'nullable|array',
            'membros.*' => 'exists:users,id'
        ]);

        foreach (collect($request->membros ?? []) as $membroId) {
            $projeto->membros()->syncWithoutDetaching([
                (int) $membroId => ['status' => 'pendente']
            ]);
        }

        return back();
    }

    public function aceitarConvite(Projeto $projeto)
    {
        $user = Auth::user();

        if (! $projeto->membros()->where('users.id', $user->id)->wherePivot('status', 'pendente')->exists()) {
            abort(403);
        }

        $projeto->membros()->updateExistingPivot($user->id, ['status' => 'aceito']);

        return back();
    }

    public function recusarConvite(Projeto $projeto)
    {
        $user = Auth::user();

        if (! $projeto->membros()->where('users.id', $user->id)->wherePivot('status', 'pendente')->exists()) {
            abort(403);
        }

        $projeto->membros()->updateExistingPivot($user->id, ['status' => 'recusado']);

        return back();
    }

    public function destroy(Projeto $projeto)
    {
        if ($projeto->user_id !== Auth::id()) {
            abort(403);
        }

        $projeto->delete();

        return redirect()->route('projetos');
    }

    public function sairProjeto(Projeto $projeto)
{
    $user = Auth::user();

    // criador não pode sair do próprio projeto
    if ($projeto->user_id === $user->id) {
        return back()->with('erro', 'O criador não pode sair do projeto.');
    }

    // remove o usuário da tabela pivot
    $projeto->membros()->detach($user->id);

    return redirect()
        ->route('projetos')
        ->with('sucesso', 'Você saiu do projeto.');
}

public function curtir(Projeto $projeto)
{
    $user = Auth::user();

    if (
        $projeto->curtidas()
        ->where('user_id',$user->id)
        ->exists()
    ) {

        $projeto->curtidas()
        ->detach($user->id);

    } else {

        $projeto->curtidas()
        ->attach($user->id);
    }

    return back();
}

public function comentar(
    Request $request,
    Projeto $projeto
)
{
    $request->validate([
        'comentario' => 'required|max:500'
    ]);

    $projeto->comentarios()->create([
        'comentario' => $request->comentario,
        'user_id' => Auth::id()
    ]);

    return back();
}

public function excluirComentario(
    ComentarioProjeto $comentario
)
{
    if (
        $comentario->user_id
        != Auth::id()
    ) {
        abort(403);
    }

    $comentario->delete();

    return back();
}

public function listarComentarios(Projeto $projeto)
{
    return response()->json(

        $projeto->comentarios()
            ->with('user')
            ->latest()
            ->get()

    );
}

}
