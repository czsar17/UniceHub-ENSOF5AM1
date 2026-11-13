<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $projeto->nome }} - UniceHub</title>
    <link rel="stylesheet" href="{{ asset('css/base.css') }}">
    <link rel="stylesheet" href="{{ asset('css/perfil.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="{{ route('theme.css') }}">
</head>

@php
    $podeEditar = $projeto->user_id === Auth::id()
        || $projeto->membros->contains('id', Auth::id());

    $user        = Auth::user();
    $jaGostei    = $projeto->curtidas()->where('user_id', Auth::id())->exists();
    $totalCurtidas   = $projeto->curtidas()->count();
    $totalComentarios = $projeto->comentarios()->count();

    $descricaoHtml = trim($projeto->descricao ?? '')
        ? \Illuminate\Support\Str::markdown($projeto->descricao, [
            'html_input'         => 'allow',
            'allow_unsafe_links' => false,
        ])
        : '';

    $disponiveis = \App\Models\User::where('id', '!=', Auth::id())
        ->orderBy('name')
        ->get()
        ->filter(fn($u) => ! $projeto->membros->contains('id', $u->id));
@endphp

<body>

{{-- ══════ MODAL DE CONVITE (oculto por padrão) ══════ --}}
@if($podeEditar)
<div id="modalConvite" class="modal-overlay" hidden>
    <div class="modal-box" role="dialog" aria-modal="true">

        <div class="modal-header">
            <h3><i class="fa-solid fa-user-plus"></i> Convidar para o projeto</h3>
            <button type="button" class="modal-close" id="fecharModal" aria-label="Fechar">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div class="modal-search-wrap">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" id="buscaConvite" placeholder="Pesquisar por nome ou curso..." autocomplete="off">
        </div>

        <form action="{{ route('projetos.convidar', $projeto) }}" method="POST">
            @csrf
            <div class="modal-lista" id="listaConvite">
                @forelse($disponiveis as $u)
                    <label class="modal-user-row" data-busca="{{ strtolower($u->name . ' ' . $u->curso) }}">
                        <input type="checkbox" name="membros[]" value="{{ $u->id }}">
                        <img src="{{ asset($u->foto ?: 'images/default-user.png') }}" alt="{{ $u->name }}">
                        <div>
                            <span class="modal-user-name">{{ $u->name }}</span>
                            <span class="modal-user-curso">{{ $u->curso ?: 'Curso não informado' }} · {{ ucfirst($u->tipo ?? '') }}</span>
                        </div>
                    </label>
                @empty
                    <p class="modal-empty">Todos os usuários já são membros do projeto.</p>
                @endforelse
                <p class="modal-nenhum" id="modalNenhum">Nenhum usuário encontrado.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="cancel-profile-btn" id="cancelarModal">Cancelar</button>
                <button type="submit" class="edit-project-btn">
                    <i class="fa-solid fa-paper-plane"></i> Enviar convites
                </button>
            </div>
        </form>
    </div>
</div>
@endif

{{-- ══════ HEADER ══════ --}}
@include('partials.header')

{{-- ══════ LAYOUT ══════ --}}
<div class="main-container">
    @include('partials.sidebar')

    <main class="profile-content">

        {{-- ══════ FORM EDIÇÃO (só envolve o que precisa ser salvo) ══════ --}}
        <form id="formProjeto" method="POST" action="{{ route('projetos.update', $projeto) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            {{-- ── CABEÇALHO ── --}}
            <section class="profile-header">

                {{-- Capa --}}
                <div class="profile-picture project-profile-picture">
                    <img id="capaPreview"
                         src="{{ $projeto->capa ? asset($projeto->capa) : asset('images/loading.png') }}"
                         class="profile-pic projeto-capa-img">
                    @if($podeEditar)
                        <label class="photo-edit-action" for="capa" title="Alterar capa">
                            <i class="fa-solid fa-camera"></i>
                        </label>
                        <input type="file" id="capa" name="capa" accept="image/*" hidden>
                    @endif
                </div>

                {{-- Info --}}
                <div class="profile-info">
                    <input type="text" name="nome" value="{{ $projeto->nome }}"
                           class="nome-input campo-projeto" disabled>
                    <input type="text" name="categoria" value="{{ $projeto->categoria }}"
                           class="curso-input campo-projeto" disabled>

                    <p style="margin-bottom:4px;">
                        <i class="fa-regular fa-calendar"></i> Criado em: {{ $projeto->created_at->format('d/m/Y') }}
                    </p>
                    <p>
                        <i class="fa-regular fa-user"></i> Criado por:
                        <a href="{{ route('usuarios.show', $projeto->criador) }}">{{ $projeto->criador->name ?? 'Usuário' }}</a>
                    </p>

                    <div class="profile-stats">
                        <span>Status:
                            <select name="status" class="campo-projeto status-select" disabled>
                                @foreach(['Planejamento','Em desenvolvimento','Concluído','Arquivado'] as $s)
                                    <option value="{{ $s }}" {{ $projeto->status === $s ? 'selected' : '' }}>{{ $s }}</option>
                                @endforeach
                            </select>
                        </span>
                        <span>Membros: {{ $projeto->membros->count() }}</span>
                        <span>Tecnologias: <span id="techCountLabel">{{ count($projeto->tecnologias ?? []) }}</span></span>
                    </div>

                    @if($projeto->repo_url)
                        <p id="repoLinkWrap" style="margin-top:8px;">
                            <i class="fa-brands fa-github"></i>
                            <a href="{{ $projeto->repo_url }}" target="_blank" rel="noopener">{{ $projeto->repo_url }}</a>
                        </p>
                    @endif
                    <input type="url" name="repo_url" id="repoInput" value="{{ $projeto->repo_url }}"
                           placeholder="https://github.com/..."
                           class="campo-projeto profile-field repo-input-hidden" disabled>
                </div>

                {{-- Ações --}}
                <div class="profile-actions">

                    {{-- Curtir: usa form externo para nao aninhar no form de edicao --}}
                    <button type="submit" form="likeProjectForm" class="like-btn {{ $jaGostei ? 'liked' : '' }}">
                        <i class="{{ $jaGostei ? 'fa-solid' : 'fa-regular' }} fa-heart"></i>
                        <span id="totalCurtidas">{{ $totalCurtidas }}</span>
                    </button>

                    {{-- Comentários: rola para a aba --}}
                    <button type="button" class="comment-btn" id="btnIrComentarios">
                        <i class="fa-regular fa-comment"></i>
                        <span>{{ $totalComentarios }}</span>
                    </button>

                    @if($podeEditar)
                        <button type="button" id="btnCancelarProjeto" class="cancel-profile-btn" hidden>
                            Cancelar
                        </button>
                        <button type="button" id="btnEditarProjeto" class="edit-project-btn">
                            <i class="fa-solid fa-pen"></i> Editar Projeto
                        </button>
                        <button type="button" id="btnConvidar" class="invite-btn">
                            <i class="fa-solid fa-user-plus"></i> Convidar
                        </button>
                    @endif

                    @if($projeto->user_id !== Auth::id() && $projeto->membros->contains('id', Auth::id()))
                        <button type="button" class="leave-project-btn"
                                onclick="document.getElementById('formSair').submit()">
                            <i class="fa-solid fa-right-from-bracket"></i> Sair
                        </button>
                    @endif
                </div>
            </section>

            {{-- ── ABAS ── --}}
            <section class="profile-tabs">
                <button type="button" class="tab-btn active" data-tab="sobre">Sobre</button>
                <button type="button" class="tab-btn" data-tab="comentarios">
                    Comentários <span class="tab-badge">{{ $totalComentarios }}</span>
                </button>
                <button type="button" class="tab-btn" data-tab="membros">Membros</button>
                <button type="button" class="tab-btn" data-tab="relacionados">Relacionados</button>
                @if($podeEditar && $projeto->user_id === Auth::id())
                    <button type="button" class="tab-btn" data-tab="configuracoes">Configurações</button>
                @endif
            </section>

            {{-- ── ABA SOBRE ── --}}
            <section class="tab-content active" id="sobre">
                <div class="profile-body">
                    <div class="left-column">
                        <div class="profile-card readme-card">
                            <h2>Sobre o projeto</h2>
                            <div id="descricaoPreview" class="markdown-body {{ $descricaoHtml ? '' : 'readme-empty' }}">
                                @if($descricaoHtml) {!! $descricaoHtml !!}
                                @else <p>Este projeto ainda não possui descrição.</p>
                                @endif
                            </div>
                            <textarea name="descricao" id="projetoDescricao"
                                      class="campo-projeto markdown-editor"
                                      placeholder="Descreva o projeto com Markdown..." disabled>{{ $projeto->descricao }}</textarea>
                        </div>
                    </div>

                    <div class="right-column">
                        <div class="profile-card">
                            <div class="section-title-row">
                                <h2>Tecnologias</h2>
                                <span id="techCounterProjeto">{{ count($projeto->tecnologias ?? []) }}/8</span>
                            </div>
                            <div class="tech-editor" id="techEditorProjeto">
                                <input type="text" id="techInputProjeto" placeholder="ex: Laravel"
                                       class="profile-field" disabled>
                                <button type="button" id="btnAddTechProjeto" class="add-tech-btn" disabled>
                                    <i class="fa-solid fa-plus"></i>
                                </button>
                            </div>
                            <div class="techs" id="techListProjeto">
                                @forelse(($projeto->tecnologias ?? []) as $tech)
                                    <span class="tech-tag" data-value="{{ $tech }}">
                                        <span>#{{ $tech }}</span>
                                        <button type="button" class="remove-tech" aria-label="Remover {{ $tech }}">
                                            <i class="fa-solid fa-xmark"></i>
                                        </button>
                                        <input type="hidden" name="tecnologias[]" value="{{ $tech }}">
                                    </span>
                                @empty
                                    <p id="semTecnologias">Nenhuma tecnologia cadastrada.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </section>

        </form>{{-- FIM formProjeto — os forms abaixo são independentes --}}

        <form id="likeProjectForm" action="{{ route('projetos.curtir', $projeto) }}" method="POST" hidden>
            @csrf
        </form>

        {{-- ── ABA COMENTÁRIOS ── --}}
        <section class="tab-content" id="comentarios">
            <div class="profile-card comentarios-card">
                <h2>Comentários</h2>

                {{-- Formulário de novo comentário --}}
                <form action="{{ route('projetos.comentar', $projeto) }}" method="POST" class="comentario-form">
                    @csrf
                    <div class="comentario-input-wrap">
                        <img src="{{ asset($user->foto) }}" alt="{{ $user->name }}" class="comentario-avatar">
                        <textarea
                            name="comentario"
                            class="comentario-textarea"
                            placeholder="Escreva um comentário..."
                            rows="2"
                            maxlength="500"
                        ></textarea>
                    </div>
                    <div class="comentario-form-footer">
                        <span class="comentario-counter">0/500</span>
                        <button type="submit" class="edit-project-btn comentario-submit">
                            <i class="fa-solid fa-paper-plane"></i> Comentar
                        </button>
                    </div>
                </form>

                <hr style="border:none;border-top:1px solid #eef2f0;margin:18px 0;">

                {{-- Lista de comentários --}}
                <div class="comentarios-lista">
                    @forelse($projeto->comentarios()->with('user')->latest()->get() as $com)
                        <div class="comentario-item">
                            <a href="{{ route('usuarios.show', $com->user) }}">
                                <img src="{{ asset($com->user->foto ?: 'images/default-user.png') }}"
                                     alt="{{ $com->user->name }}" class="comentario-avatar">
                            </a>
                            <div class="comentario-body">
                                <div class="comentario-meta">
                                    <a href="{{ route('usuarios.show', $com->user) }}" class="comentario-autor">
                                        {{ $com->user->name }}
                                    </a>
                                    <span class="comentario-data">{{ $com->created_at->diffForHumans() }}</span>
                                    @if($com->user_id === Auth::id())
                                        <form action="{{ route('comentarios.excluir', $com) }}" method="POST"
                                              style="display:inline;"
                                              onsubmit="return confirm('Excluir comentário?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="comentario-excluir" title="Excluir">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                                <p class="comentario-texto">{{ $com->comentario }}</p>
                            </div>
                        </div>
                    @empty
                        <p style="color:#6b8a82;text-align:center;padding:24px 0;">
                            Nenhum comentário ainda. Seja o primeiro!
                        </p>
                    @endforelse
                </div>
            </div>
        </section>

        {{-- ── ABA MEMBROS ── --}}
        <section class="tab-content" id="membros">
            <div class="profile-card" style="height:calc(100vh - 320px);display:flex;flex-direction:column;">
                <h2>Membros do projeto</h2>
                <div class="profile-users-grid" style="flex:1;overflow-y:auto;margin-top:12px;padding-right:8px;">
                    @forelse($projeto->membros as $membro)
                        <a href="{{ route('usuarios.show', $membro) }}" class="profile-user-card">
                            <img src="{{ $membro->foto ? asset($membro->foto) : asset('images/default-user.png') }}"
                                 alt="{{ $membro->name }}">
                            <div>
                                <h3>{{ $membro->name }}</h3>
                                @if($membro->isVerifiedProfessor())
                                    <span class="teacher-verified-badge compact"><i class="fa-solid fa-circle-check"></i> Professor verificado</span>
                                @else
                                    <span>{{ $membro->curso ?: 'Curso não informado' }}</span>
                                @endif
                            </div>
                        </a>
                    @empty
                        <p>Este projeto ainda não possui membros aceitos.</p>
                    @endforelse
                </div>
            </div>
        </section>

        {{-- ── ABA RELACIONADOS ── --}}
        <section class="tab-content" id="relacionados">
            <div class="profile-card">
                <h2>Projetos relacionados</h2>
                <div class="profile-projects-grid" style="margin-top:12px;">
                    @forelse($projetosRelacionados as $relacionado)
                        <a href="{{ route('projetos.show', $relacionado) }}" class="profile-project-card">
                            <img src="{{ $relacionado->capa ? asset($relacionado->capa) : asset('images/loading.png') }}" alt="">
                            <div>
                                <div class="profile-project-title">
                                    <h3>{{ $relacionado->nome }}</h3>
                                    <span>{{ $relacionado->status }}</span>
                                </div>
                                <p>{{ Str::limit(strip_tags($relacionado->descricao), 120) }}</p>
                            </div>
                        </a>
                    @empty
                        <p>Nenhum projeto relacionado encontrado.</p>
                    @endforelse
                </div>
            </div>
        </section>

        {{-- ── ABA CONFIGURAÇÕES ── --}}
        @if($podeEditar && $projeto->user_id === Auth::id())
            <section class="tab-content" id="configuracoes">
                <div class="profile-card" style="border:1px solid #ffd6d6;">
                    <h2 style="color:#8f1f1f;">Zona de perigo</h2>
                    <p style="margin:10px 0 16px;color:#666;">Esta ação é permanente e não pode ser desfeita.</p>
                    <form action="{{ route('projetos.destroy', $projeto) }}" method="POST"
                          onsubmit="return confirm('Tem certeza que deseja excluir este projeto?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="leave-project-btn">
                            <i class="fa-solid fa-trash"></i> Excluir projeto
                        </button>
                    </form>
                </div>
            </section>
        @endif

        {{-- Form sair --}}
        @if($projeto->user_id !== Auth::id() && $projeto->membros->contains('id', Auth::id()))
            <form id="formSair" action="{{ route('projetos.sair', $projeto) }}" method="POST"
                  onsubmit="return confirm('Deseja realmente sair deste projeto?')" hidden>
                @csrf
                @method('DELETE')
            </form>
        @endif

    </main>
</div>

<script src="{{ asset('js/perfil.js') }}"></script>
  <script src="{{ asset('js/notifications.js') }}"></script>
</body>
</html>