<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Conexões - UniceHub</title>

    <link rel="stylesheet" href="{{ asset('css/base.css') }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">

    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <link rel="stylesheet" href="{{ route('theme.css') }}">
</head>

<body>
 <!-- HEADER -->
  @include('partials.header')

    <div class="main-container">

        <!-- SIDEBAR -->
        @include('partials.sidebar')    
        <main class="connections-container">

    <!-- HEADER -->
    <section class="connections-header">

        <div>
            <h1>Conexões</h1>
            <p>Conecte-se com pessoas e colabore em projetos juntos.</p>
        </div>

    </section>

    <!-- CONTEÚDO -->
    <section class="connections-content">

        <!-- ESQUERDA -->
        <div class="connections-main">

            <!-- TABS -->
           <div class="connections-tabs">

    <button class="tab-btn active" data-tab="todos">Todos</button>

    <button class="tab-btn" data-tab="seguidores">
        Seguidores
    </button>

    <button class="tab-btn" data-tab="seguindo">
        Seguindo
    </button>

    <button class="tab-btn" data-tab="solicitacoes">
        Solicitações
    </button>

    <button class="tab-btn" data-tab="bloqueados">
        Bloqueados
    </button>

</div>

            <!-- TOPO LISTA -->
            <div class="connections-top">

                <h2 id="section-title">Todas as conexões</h2>
                <span class="counter">
                    0 conexões
                </span>

                <form class="search-box" action="{{ route('buscar') }}" method="GET">

                    <input
                        type="text"
                        name="q"
                        value="{{ request('q') }}"
                        placeholder="Buscar pessoas e projetos..."
                    >

                    <button type="submit" aria-label="Pesquisar"><i class="fa-solid fa-magnifying-glass"></i></button>

                </form>

            </div>

            <!-- LISTA -->
            <div class="connections-list">

    {{-- TODOS --}}
    <div class="tab-content active" id="todos">

        @php
            $todos = $seguidores->map(fn($f) => $f->seguidor)
                ->merge($seguindo->map(fn($f) => $f->seguido))
                ->unique('id');
        @endphp

        @forelse($todos as $usuario)

        <div class="connection-card profile-nav-card" data-url="{{ route('usuarios.show', $usuario) }}">

            <div class="connection-user">

                <img src="{{ asset($usuario->foto) }}">

                <div>

                    <h4>{{ $usuario->name }}</h4>

                    <span>{{ $usuario->curso }}</span>

                </div>

            </div>

            <div class="connection-actions">

                <div class="dropdown">

                    <i class="fa-solid fa-ellipsis"></i>

                    <div class="dropdown-menu">

                        <form action="{{ route('usuario.bloquear', $usuario->id) }}" method="POST">
                            @csrf
                            <button type="submit">
                                Bloquear
                            </button>
                        </form>

                    </div>

                </div>

            </div>

        </div>

        @empty

        <div class="empty-connections">

            <i class="fa-solid fa-user-group"></i>

            <h3>Nenhuma conexão encontrada</h3>

            <p>Comece encontrando pessoas para se conectar.</p>

        </div>

        @endforelse

    </div>

    {{-- SEGUIDORES --}}
    <div class="tab-content" id="seguidores">

        @forelse($seguidores as $follow)

        <div class="connection-card profile-nav-card" data-url="{{ route('usuarios.show', $follow->seguidor) }}">

            <div class="connection-user">

                <img src="{{ asset($follow->seguidor->foto) }}">

                <div>

                    <h4>{{ $follow->seguidor->name }}</h4>

                    <span>{{ $follow->seguidor->curso }}</span>

                </div>

            </div>

        </div>

        @empty

        <div class="empty-connections">
            <i class="fa-solid fa-users"></i>
            <h3>Nenhum seguidor encontrado</h3>
        </div>

        @endforelse

    </div>

    {{-- SEGUINDO --}}
    <div class="tab-content" id="seguindo">

        @forelse($seguindo as $follow)

        <div class="connection-card profile-nav-card" data-url="{{ route('usuarios.show', $follow->seguido) }}">

            <div class="connection-user">

                <img src="{{ asset($follow->seguido->foto) }}">

                <div>

                    <h4>{{ $follow->seguido->name }}</h4>

                    <span>{{ $follow->seguido->curso }}</span>

                </div>

            </div>

        </div>

        @empty

        <div class="empty-connections">
            <i class="fa-solid fa-user-check"></i>
            <h3>Você não segue ninguém</h3>
        </div>

        @endforelse

    </div>

    {{-- SOLICITAÇÕES --}}
    <div class="tab-content" id="solicitacoes">

        @foreach($solicitacoes as $solicitacao)

        <div class="request-card profile-nav-card" data-url="{{ route('usuarios.show', $solicitacao->seguidor) }}">

            <div class="connection-user">

                <img src="{{ asset($solicitacao->seguidor->foto) }}">

                <div>

                    <div class="request-title">
                        <h4>{{ $solicitacao->seguidor->name }}</h4>
                        <span class="request-tag follow-tag">Conexão</span>
                    </div>

                    <span>{{ $solicitacao->seguidor->curso }}</span>

                </div>

            </div>

            <div class="request-actions">

                <form action="{{ route('seguir.aceitar', $solicitacao->id) }}" method="POST">
                    @csrf
                    <button class="accept-btn">
                        Aceitar
                    </button>
                </form>

                <form action="{{ route('seguir.recusar', $solicitacao->id) }}" method="POST">
                    @csrf
                    <button class="remove-btn">
                        Remover
                    </button>
                </form>

            </div>

        </div>

        @endforeach

        @foreach($solicitacoesProjeto as $projeto)

        <div class="request-card project-request profile-nav-card" data-url="{{ route('projetos.show', $projeto) }}">

            <div class="connection-user">

                <img src="{{ $projeto->capa ? asset($projeto->capa) : asset('images/loading.png') }}">

                <div>

                    <div class="request-title">
                        <h4>{{ $projeto->nome }}</h4>
                        <span class="request-tag project-tag">Projeto</span>
                    </div>

                    <span>Convite de {{ $projeto->criador->name ?? 'um usuário' }}</span>

                </div>

            </div>

            <div class="request-actions">

                <form action="{{ route('projetos.aceitar', $projeto) }}" method="POST">
                    @csrf
                    <button class="accept-btn">
                        Aceitar
                    </button>
                </form>

                <form action="{{ route('projetos.recusar', $projeto) }}" method="POST">
                    @csrf
                    <button class="remove-btn">
                        Remover
                    </button>
                </form>

            </div>

        </div>

        @endforeach

        @if($solicitacoes->isEmpty() && $solicitacoesProjeto->isEmpty())

        <div class="empty-connections">
            <i class="fa-solid fa-user-clock"></i>
            <h3>Nenhuma solicitação pendente</h3>
        </div>

        @endif

    </div>

</div>
            
        </div>

        <!-- DIREITA -->
        <aside class="connections-sidebar">

            <!-- SUGESTÕES -->
            <div class="sidebar-card">

                <div class="card-header">

                    <h3>Sugestões para você</h3>

                    <a href="#">
                        Ver todas
                    </a>

                </div>

                <div class="suggestions-list">

    @forelse($sugestoes as $usuario)

    <div class="suggestion-item profile-nav-card" data-url="{{ route('usuarios.show', $usuario) }}">

        <img src="{{ asset($usuario->foto) }}">

        <div>

            <h4>{{ $usuario->name }}</h4>

            <span>{{ $usuario->curso }}</span>

        </div>

        <form action="{{ route('seguir.enviar', $usuario->id) }}" method="POST">

            @csrf

            <button class="mini-connect-btn">
                Conectar
            </button>

        </form>

    </div>

    @empty

    <p class="empty-sidebar">
        Nenhuma sugestão disponível.
    </p>

    @endforelse

</div>

<a href="#" class="see-more">
    Ver mais sugestões >
</a>

            </div>

            <!-- SOLICITAÇÕES -->
            <div class="sidebar-card">

                <div class="card-header">

                    <h3>Solicitações de conexões</h3>

                </div>

               <div class="requests-list">

    @foreach($solicitacoes->take(3) as $solicitacao)

    <div class="request-item profile-nav-card" data-url="{{ route('usuarios.show', $solicitacao->seguidor) }}">

        <img src="{{ asset($solicitacao->seguidor->foto) }}">

        <div class="request-info">

            <div class="request-title">
                <h4>{{ $solicitacao->seguidor->name }}</h4>
                <span class="request-tag follow-tag">Conexão</span>
            </div>

            <span>{{ $solicitacao->seguidor->curso }}</span>

            <div class="request-buttons">

                <form action="{{ route('seguir.aceitar', $solicitacao->id) }}" method="POST">
                    @csrf

                    <button class="accept-btn">
                        Aceitar
                    </button>

                </form>

                <form action="{{ route('seguir.recusar', $solicitacao->id) }}" method="POST">
                    @csrf

                    <button class="remove-btn">
                        Remover
                    </button>

                </form>

            </div>

        </div>

    </div>

    @endforeach

    @foreach($solicitacoesProjeto->take(3) as $projeto)

    <div class="request-item project-request profile-nav-card" data-url="{{ route('projetos.show', $projeto) }}">

        <img src="{{ $projeto->capa ? asset($projeto->capa) : asset('images/loading.png') }}">

        <div class="request-info">

            <div class="request-title">
                <h4>{{ $projeto->nome }}</h4>
                <span class="request-tag project-tag">Projeto</span>
            </div>

            <span>Convite de {{ $projeto->criador->name ?? 'um usuário' }}</span>

            <div class="request-buttons">

                <form action="{{ route('projetos.aceitar', $projeto) }}" method="POST">
                    @csrf

                    <button class="accept-btn">
                        Aceitar
                    </button>

                </form>

                <form action="{{ route('projetos.recusar', $projeto) }}" method="POST">
                    @csrf

                    <button class="remove-btn">
                        Remover
                    </button>

                </form>

            </div>

        </div>

    </div>

    @endforeach

    @if($solicitacoes->isEmpty() && $solicitacoesProjeto->isEmpty())

    <p class="empty-sidebar">
        Nenhuma solicitação.
    </p>

    @endif

</div>


                     
                      
                    

                </div>

                <a href="#" class="see-more">
                    Ver todas as solicitações >
                </a>

            </div>

        </aside>

    </section>

</main>
</div>
<script>
const tabs = document.querySelectorAll(".tab-btn");
const contents = document.querySelectorAll(".tab-content");
const sectionTitle = document.getElementById("section-title");
const counter = document.querySelector(".counter");

const titleByTab = {
    todos: "Todas as conexões",
    seguidores: "Seguidores",
    seguindo: "Seguindo",
    solicitacoes: "Solicitações",
    bloqueados: "Bloqueados",
};

function updateCounter(tabName){
    const activeContent = document.getElementById(tabName);
    if (!activeContent || !counter) return;

    const cards = activeContent.querySelectorAll(".connection-card, .request-card");
    counter.textContent = `${cards.length} ${cards.length === 1 ? "item" : "itens"}`;
}

tabs.forEach((tab) => {
    tab.addEventListener("click", () => {
        tabs.forEach((btn) => btn.classList.remove("active"));
        contents.forEach((content) => content.classList.remove("active"));

        tab.classList.add("active");
        document.getElementById(tab.dataset.tab).classList.add("active");
        sectionTitle.textContent = titleByTab[tab.dataset.tab] || "Conexões";
        updateCounter(tab.dataset.tab);
    });
});

document.addEventListener("click", (event) => {
    const card = event.target.closest(".profile-nav-card");

    if (!card || event.target.closest("button, a, form, input, .dropdown, .dropdown-menu")) {
        return;
    }

    window.location.href = card.dataset.url;
});

updateCounter("todos");
</script>
  <script src="{{ asset('js/notifications.js') }}"></script>
</body>
</html>