<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <link rel="stylesheet" href="{{ asset('css/base.css') }}">
    <link rel="stylesheet" href="{{ asset('css/perfil.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="{{ route('theme.css') }}">
</head>

@php
    $authUser = Auth::user();
    $user = $perfilUser ?? $authUser;
    $isOwnProfile = $user->id === $authUser->id;
    $projetosPerfil = $projetosPerfil ?? collect();
    $perfilFollowStatus = $perfilFollowStatus ?? null;
    $seguidoresCount = $user->seguidores()->wherePivot('status', 'aceito')->count();
    $seguindoCount = $user->seguindo()->wherePivot('status', 'aceito')->count();
    $cursos = [
        'ADS' => 'ADS',
        'Análise e Desenvolvimento de Sistemas' => 'Análise e Desenvolvimento de Sistemas',
        'Engenharia de Software' => 'Engenharia de Software',
        'Ciência da Computação' => 'Ciência da Computação',
    ];
    $tecnologias = old('tecnologias', $user->tecnologias ?? []);

    if (is_string($tecnologias)) {
        $tecnologias = array_filter(array_map('trim', explode(',', $tecnologias)));
    }

    $interessesMarkdown = old('interesses_markdown', $user->interesses_markdown ?? '');
    $interessesHtml = trim($interessesMarkdown)
        ? \Illuminate\Support\Str::markdown($interessesMarkdown, [
            'html_input' => 'allow',
            'allow_unsafe_links' => false,
        ])
        : '';
@endphp

<body>
    @include('partials.header')

    <div class="main-container">
        @include('partials.sidebar')

        <main class="profile-content">
            @if ($errors->any())
                <div class="profile-alert profile-alert-error">
                    {{ $errors->first() }}
                </div>
            @endif

            <form id="formPerfil" action="{{ $isOwnProfile ? '/perfil/atualizar' : '#' }}" method="POST" enctype="multipart/form-data">
                @csrf

                <section class="profile-header">
                    <div class="profile-picture">
                        <img src="{{ asset($user->foto) }}" class="profile-pic">
                        @if($isOwnProfile)
                            <label class="photo-edit-action" for="foto" aria-label="Alterar foto">
                                <i class="fa-solid fa-camera"></i>
                            </label>
                            <input type="file" name="foto" id="foto" hidden disabled class="campo-edicao" accept="image/*">
                        @endif
                    </div>

                    <div class="profile-info">
                        <input
                            type="text"
                            name="name"
                            value="{{ old('name', $user->name) }}"
                            disabled
                            required
                            maxlength="255"
                            class="campo-edicao nome-input">

                        <select name="curso" disabled class="campo-edicao curso-input">
                            <option value="">Selecione seu curso</option>
                            @foreach($cursos as $valor => $rotulo)
                                <option value="{{ $valor }}" @selected(old('curso', $user->curso) === $valor)>
                                    {{ $rotulo }}
                                </option>
                            @endforeach
                        </select>

                        @if($user->isVerifiedProfessor())
                            <div class="teacher-verified-badge profile"><i class="fa-solid fa-circle-check"></i> Professor verificado</div>
                        @endif

                        <p>
                            <i class="fa-regular fa-calendar"></i>
                            Membro desde: {{ $user->created_at->format('d/m/Y') }}
                        </p>

                        <div class="profile-stats">
                            <span>Projetos: {{ $projetosPerfil->count() }}</span>
                            <span>Seguidores: {{ $seguidoresCount }}</span>
                            <span>Seguindo: {{ $seguindoCount }}</span>
                        </div>
                    </div>

                    <div class="profile-actions">
                        @if($isOwnProfile)
                            <button type="button" id="btnCancelar" class="cancel-profile-btn" hidden>
                                Cancelar
                            </button>

                            <button type="button" id="btnEditar" class="edit-project-btn">
                                <i class="fa-solid fa-pen"></i>
                                Editar Perfil
                            </button>
                        @else
                            @if($perfilFollowStatus === 'aceito')
                                <button type="button" class="edit-project-btn profile-status-btn" disabled>
                                    <i class="fa-solid fa-user-check"></i>
                                    Conectado
                                </button>
                            @elseif($perfilFollowStatus === 'pendente')
                                <button type="button" class="edit-project-btn profile-status-btn" disabled>
                                    <i class="fa-solid fa-clock"></i>
                                    Solicitação enviada
                                </button>
                            @else
                                <button type="submit" class="edit-project-btn" form="followUserForm">
                                    <i class="fa-solid fa-user-plus"></i>
                                    Conectar
                                </button>
                            @endif
                        @endif
                    </div>
                </section>

                <section class="profile-tabs">
                    <button type="button" class="tab-btn active" data-tab="sobre">Sobre</button>
                    <button type="button" class="tab-btn" data-tab="interesses">Interesses</button>
                    <button type="button" class="tab-btn" data-tab="projetos">Projetos</button>
                </section>

                <section class="tab-content active" id="sobre">
                    <div class="profile-body">
                        <div class="left-column">
                            <div class="profile-card">
                                <h2>Sobre mim</h2>

                                <textarea
                                    name="sobre_mim"
                                    id="sobre_mim"
                                    maxlength="350"
                                    disabled
                                    class="campo-edicao textarea-perfil"
                                    placeholder="Conte um pouco sobre você">{{ old('sobre_mim', $user->sobre_mim) }}</textarea>

                                <div class="char-counter">
                                    <span id="sobreContador">0</span>/350 caracteres
                                </div>

                                <hr>

                                <div class="profile-contact-grid">
                                    <label>
                                        <span>Email</span>
                                        <input
                                            type="email"
                                            name="email"
                                            value="{{ old('email', $user->email) }}"
                                            disabled
                                            required
                                            maxlength="255"
                                            class="campo-edicao profile-field">
                                    </label>

                                    <label>
                                        <span>Número</span>
                                        <input
                                            type="tel"
                                            name="telefone"
                                            value="{{ old('telefone', $user->telefone) }}"
                                            disabled
                                            maxlength="20"
                                            placeholder="(00) 00000-0000"
                                            class="campo-edicao profile-field">
                                    </label>
                                </div>

                                <hr>

                                <div class="section-title-row">
                                    <h2>Tecnologias</h2>
                                    <span id="techCounter">0/8</span>
                                </div>

                                <div class="tech-editor">
                                    <input
                                        type="text"
                                        id="techInput"
                                        disabled
                                        maxlength="30"
                                        class="campo-edicao profile-field"
                                        placeholder="Digite uma tecnologia">

                                    <button type="button" id="btnAddTech" class="add-tech-btn" disabled>
                                        <i class="fa-solid fa-plus"></i>
                                    </button>
                                </div>

                                <div class="techs" id="techList">
                                    @foreach(array_slice($tecnologias, 0, 8) as $tecnologia)
                                        <span class="tech-tag" data-value="{{ $tecnologia }}">
                                            #{{ $tecnologia }}
                                            <button type="button" class="remove-tech" aria-label="Remover {{ $tecnologia }}">
                                                <i class="fa-solid fa-xmark"></i>
                                            </button>
                                            <input type="hidden" name="tecnologias[]" value="{{ $tecnologia }}">
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <div class="right-column">
                            <div class="profile-card">
                                <h2>Atividades Recentes</h2>

                                @forelse($atividades as $atividade)
                                    <div class="atividade-item">
                                        <p>{{ $atividade->descricao }}</p>
                                    </div>
                                @empty
                                    <p>Esse usuário ainda não possui atividades recentes.</p>
                                @endforelse

                        
                            </div>
                        </div>
                    </div>
                </section>

                <section class="tab-content" id="interesses">
                    <div class="profile-card readme-card">
                        <div class="section-title-row">
                            <h2>README de interesses</h2>
                            <span>Markdown</span>
                        </div>

                        <textarea
                            name="interesses_markdown"
                            id="interesses_markdown"
                            disabled
                            class="campo-edicao markdown-editor"
                            placeholder="# Meus interesses

Sou uma pessoa interessada em desenvolvimento web, projetos colaborativos e tecnologia aplicada.

## Atualmente estudando
- Laravel
- JavaScript
- Banco de dados

```js
console.log('UniceHub');
```">{{ $interessesMarkdown }}</textarea>

                        <div class="readme-preview markdown-body {{ $interessesHtml ? '' : 'readme-empty' }}">
                            @if($interessesHtml)
                                {!! $interessesHtml !!}
                            @else
                                <p>Seu README de interesses vai aparecer aqui depois que voce escrever em Markdown e salvar.</p>
                                <pre><code># Meus interesses

- Desenvolvimento web
- Projetos academicos
- Inteligencia artificial</code></pre>
                            @endif
                        </div>
                    </div>
                </section>

                <section class="tab-content" id="projetos">
                    <div class="profile-card">
                        <h2>{{ $isOwnProfile ? 'Meus Projetos' : 'Projetos de ' . $user->name }}</h2>

                        <div class="profile-projects-grid">
                            @forelse($projetosPerfil as $projeto)
                                <a href="{{ route('projetos.show', $projeto) }}" class="profile-project-card">
                                    <img src="{{ $projeto->capa ? asset($projeto->capa) : asset('images/loading.png') }}" alt="">
                                    <div>
                                        <div class="profile-project-title">
                                            <h3>{{ $projeto->nome }}</h3>
                                            <span>{{ $projeto->status }}</span>
                                        </div>
                                        <p>{{ Str::limit(strip_tags($projeto->descricao), 120) }}</p>
                                        <div class="profile-project-meta">
                                            <span>{{ $projeto->membros->count() }} membros</span>
                                            <span>{{ $projeto->created_at->format('d/m/Y') }}</span>
                                        </div>
                                    </div>
                                </a>
                            @empty
                                <p>{{ $isOwnProfile ? 'Você ainda não participa de projetos.' : 'Esse usuário ainda não participa de projetos.' }}</p>
                            @endforelse
                        </div>
                    </div>
                </section>

                <section class="tab-content" id="atividades">
                    <div class="profile-card">
                        <h2>Atividades Recentes</h2>
                    </div>
                </section>
            </form>
            @if(!$isOwnProfile && !$perfilFollowStatus)
                <form id="followUserForm" action="{{ route('seguir.enviar', $user->id) }}" method="POST" hidden>
                    @csrf
                </form>
            @endif
        </main>
    </div>

    <script src="{{ asset('js/perfil.js') }}"></script>
  <script src="{{ asset('js/notifications.js') }}"></script>
</body>

</html>