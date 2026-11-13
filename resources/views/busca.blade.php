<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Busca - UniceHub</title>
    <link rel="stylesheet" href="{{ asset('css/base.css') }}">
    <link rel="stylesheet" href="{{ asset('css/perfil.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="{{ route('theme.css') }}">
</head>

@php($user = Auth::user())

<body>
    @include('partials.header')

    <div class="main-container">
        @include('partials.sidebar')

        <main class="profile-content search-page-content">
            <section class="profile-header search-page-header">
                <div class="profile-info">
                    <h1 class="profile-title-text">Busca</h1>
                    <h2>{{ $termo ? 'Resultados para "' . $termo . '"' : 'Pesquise pessoas e projetos' }}</h2>
                    <div class="profile-stats">
                        <span>Pessoas: {{ $usuarios->count() }}</span>
                        <span>Projetos: {{ $projetos->count() }}</span>
                    </div>
                </div>
            </section>

            <section class="profile-tabs">
                <button type="button" class="tab-btn active" data-tab="pessoas">Pessoas</button>
                <button type="button" class="tab-btn" data-tab="projetos">Projetos</button>
            </section>

            <div class="search-results">
    @forelse($usuarios as $usuario)
        <a href="{{ route('usuarios.show', $usuario) }}" class="search-user-card">
            <img src="{{ asset($usuario->foto) }}" alt="">

            <div class="search-user-info">
                <h3>{{ $usuario->name }}</h3>
                @if($usuario->isVerifiedProfessor())
                    <p class="teacher-verified-badge compact"><i class="fa-solid fa-circle-check"></i> Professor verificado</p>
                @else
                    <p>{{ $usuario->curso ?: 'Curso não informado' }}</p>
                @endif
            </div>

            <i class="fa-solid fa-chevron-right"></i>
        </a>
    @empty
        <p>Nenhuma pessoa encontrada.</p>
    @endforelse
</div>

            <section class="tab-content" id="projetos">
                <div class="profile-card">
                    <h2>Projetos encontrados</h2>
                    <div class="profile-projects-grid">
                        @forelse($projetos as $projeto)
                            <a href="{{ route('projetos.show', $projeto) }}" class="profile-project-card">
                                <img src="{{ $projeto->capa ? asset($projeto->capa) : asset('images/loading.png') }}" alt="">
                                <div>
                                    <div class="profile-project-title">
                                        <h3>{{ $projeto->nome }}</h3>
                                        <span>{{ $projeto->status }}</span>
                                    </div>
                                    <p>{{ Str::limit(strip_tags($projeto->descricao), 120) }}</p>
                                    <div class="profile-project-meta">
                                        <span>{{ $projeto->criador->name ?? 'Criador não informado' }}</span>
                                        @if($projeto->criador?->isVerifiedProfessor())
                                            <span class="teacher-verified-badge mini"><i class="fa-solid fa-circle-check"></i></span>
                                        @endif
                                        <span>{{ $projeto->membros->count() }} membros</span>
                                    </div>
                                </div>
                            </a>
                        @empty
                            <p>{{ $termo ? 'Nenhum projeto encontrado.' : 'Digite algo na barra de pesquisa.' }}</p>
                        @endforelse
                    </div>
                </div>
            </section>
        </main>
    </div>

    <script src="{{ asset('js/perfil.js') }}"></script>
  <script src="{{ asset('js/notifications.js') }}"></script>
</body>
</html>
