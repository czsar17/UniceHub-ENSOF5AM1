@php
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
@endphp

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>UniceHub - Home</title>
  <link rel="stylesheet" href="{{ asset('css/base.css') }}">
  <link rel="stylesheet" href="{{ asset('css/home.css') }}">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"/>
    <link rel="stylesheet" href="{{ route('theme.css') }}">
</head>

<body>

  <!-- HEADER -->
  @include('partials.header')

  <!-- CONTEÚDO -->
  <main class="main-container">

    <!-- SIDEBAR -->
    @include('partials.sidebar')

    <!-- FEED -->
    <section class="feed">

      @forelse($projetosFeed as $projeto)

        <div class="post-card">

          {{-- Topo: criador --}}
          <div class="post-top">
            <a href="{{ route('usuarios.show', $projeto->criador) }}" class="post-user">
              <img src="{{ asset($projeto->criador->foto ?? 'images/default-user.png') }}">
              <div>
                <h3>{{ $projeto->criador->name }}</h3>
                @if($projeto->criador?->isVerifiedProfessor())
                  <span class="teacher-verified-badge compact"><i class="fa-solid fa-circle-check"></i> Professor verificado</span>
                @else
                  <span>{{ $projeto->criador->curso }}</span>
                @endif
              </div>
            </a>

            <div class="post-right">
              <span>{{ $projeto->created_at->diffForHumans() }}</span>
              <div class="options-area">
                <i class="fa-solid fa-ellipsis options-btn"></i>
                <div class="mini-option">
                  <div class="dismiss-post">
                    <i class="fa-solid fa-xmark"></i> Dispensar
                  </div>
                </div>
              </div>
            </div>
          </div>

          {{-- Conteúdo — clicável para abrir o projeto --}}
          @php
            $descHtml = trim($projeto->descricao ?? '')
              ? \Illuminate\Support\Str::markdown($projeto->descricao, [
                  'html_input'         => 'allow',
                  'allow_unsafe_links' => false,
                ])
              : '';
            // Limita o HTML renderizado a ~3 linhas via CSS, não truncando o markdown cru
          @endphp
          <a href="{{ route('projetos.show', $projeto) }}" class="post-content-link">
            <div class="post-content">
              @if($projeto->capa)
                <img src="{{ asset($projeto->capa) }}" class="post-capa" alt="{{ $projeto->nome }}">
              @endif
              <h2>{{ $projeto->nome }}</h2>
              @if($descHtml)
                <div class="post-desc markdown-feed">
                  {!! $descHtml !!}
                </div>
              @endif
              @if(!empty($projeto->tecnologias))
                <div class="post-techs">
                  @foreach(array_slice($projeto->tecnologias, 0, 5) as $tech)
                    <span>#{{ $tech }}</span>
                  @endforeach
                </div>
              @endif
            </div>
          </a>

          {{-- Ações: curtir + comentários + ver projeto --}}
          <div class="post-actions">
            <div class="left-actions">

              {{-- Curtir --}}
              <form action="{{ route('projetos.curtir', $projeto) }}" method="POST">
                @csrf
                @php $jaGostei = $projeto->curtidas->contains('id', Auth::id()); @endphp
                <button type="submit" class="like-btn {{ $jaGostei ? 'liked' : '' }}">
                  <i class="{{ $jaGostei ? 'fa-solid' : 'fa-regular' }} fa-heart"></i>
                  {{ $projeto->curtidas_count }}
                </button>
              </form>

              {{-- Comentários — abre o modal existente --}}
              <button class="comment-btn" data-id="{{ $projeto->id }}">
                <i class="fa-regular fa-comment"></i>
                {{ $projeto->comentarios_count }} Comentários
              </button>

            </div>

            {{-- Ver projeto --}}
            <a href="{{ route('projetos.show', $projeto) }}" class="ver-projeto-btn">
              Ver projeto <i class="fa-solid fa-arrow-right"></i>
            </a>
          </div>

        </div>

      @empty
        <div class="post-card">
          <h3>Nenhum projeto encontrado.</h3>
        </div>
      @endforelse

    </section>

    <!-- WIDGETS -->
    <aside class="widgets">

      <div class="widget-card">
        <div class="widget-title">
          <h3>Sugestões de Conexões</h3>
          <button class="widget-toggle" type="button" aria-label="Recolher card" aria-expanded="true"><i class="fa-solid fa-ellipsis"></i></button>
        </div>
        <div class="widget-body">

        @if($sugestoes->count() > 0)
          @foreach($sugestoes as $usuario)
            <div class="suggestion">
              <div class="suggestion-user">
                <img src="{{ asset($usuario->foto) }}">
                <div>
                  <h4>{{ $usuario->name }}</h4>
                  @if($usuario->curso)<span>{{ $usuario->curso }}</span>@endif
                </div>
              </div>
              <form action="{{ route('seguir.enviar', $usuario->id) }}" method="POST">
                @csrf
                <button type="submit">Conectar</button>
              </form>
            </div>
          @endforeach
        @else
          <div class="empty-suggestions">
            <i class="fa-solid fa-user-group"></i>
            <p>Nenhum usuário encontrado para se conectar.</p>
          </div>
        @endif
        </div>
      </div>

      <div class="widget-card">
        <div class="widget-title">
          <h3><i class="fa-solid fa-laptop-code"></i> Projetos em Destaque</h3>
          <button class="widget-toggle" type="button" aria-label="Recolher card" aria-expanded="true"><i class="fa-solid fa-ellipsis"></i></button>
        </div>
        <div class="widget-body">

        @forelse($projetosDestaque as $projetoDestaque)
          <div class="project-mini-card">
            <div>
              <h4>{{ $projetoDestaque->nome }}</h4>
              <p>{{ Str::limit(strip_tags($projetoDestaque->descricao), 90) }}</p>
              <div class="project-info">
                <span><i class="fa-solid fa-heart"></i> {{ $projetoDestaque->curtidas_count }}</span>
                <span><i class="fa-regular fa-comment"></i> {{ $projetoDestaque->comentarios_count }}</span>
                <span><i class="fa-solid fa-users"></i> {{ $projetoDestaque->membros->count() }} membros</span>
              </div>
            </div>
            <a href="{{ route('projetos.show', $projetoDestaque) }}">Ver projeto</a>
          </div>
        @empty
          <div class="empty-suggestions">
            <i class="fa-regular fa-folder-open"></i>
            <p>Nenhum projeto com interações ainda.</p>
          </div>
        @endforelse
        </div>
      </div>

      <div class="widget-card">
        <div class="widget-title">
          <h3>Tecnologias em alta</h3>
          <button class="widget-toggle" type="button" aria-label="Recolher card" aria-expanded="true"><i class="fa-solid fa-ellipsis"></i></button>
        </div>
        <div class="widget-body">
        <div class="tags">
          @forelse($tecnologiasEmAlta as $tecnologia)
            <span>#{{ $tecnologia['nome'] }} <small>{{ $tecnologia['total'] }}</small></span>
          @empty
            <span>#Sem tags ainda</span>
          @endforelse
        </div>
        </div>
      </div>

    </aside>

  </main>

  <!-- MODAL DE COMENTÁRIOS (home) -->
  <div id="commentModal" class="comment-modal">
    <div class="comment-box">
      <div class="comment-header">
        <h3>Comentários</h3>
        <button id="closeComments"><i class="fa-solid fa-xmark"></i></button>
      </div>
      <div id="commentsContainer"></div>
      <form id="commentForm" method="POST">
        @csrf
        <input type="text" name="comentario" id="commentText" placeholder="Digite um comentário...">
        <button type="submit">Enviar</button>
      </form>
    </div>
  </div>

  <script src="{{ asset('js/home.js') }}"></script>
  <script src="{{ asset('js/notifications.js') }}"></script>
</body>
</html>