<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Projetos - UniceHub</title>
  <link rel="stylesheet" href="{{ asset('css/base.css') }}">
<link rel="stylesheet" href="{{ asset('css/app.css') }}">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="{{ route('theme.css') }}">
</head>

<body>

  <!-- HEADER -->
  @include('partials.header')

  <div class="main-container">

    <!-- SIDEBAR -->
    @include('partials.sidebar')

    <!-- CONTEÚDO -->
    <div class="projects-content">

      <div class="projects-left">

        <div class="projects-header">
          <div>
            <h1>Meus Projetos</h1>
            <p>Gerencie e acompanhe seus projetos.</p>
          </div>
          <div class="project-actions">
            <a href="{{ route('projetoscad') }}">
              <button class="new-project-btn" type="button">
                <i class="fa-solid fa-plus"></i> Novo Projeto
              </button>
            </a>
          </div>
        </div>

        {{-- Convites pendentes --}}
        @if($convites->count() > 0)
          <div class="convites-section">
            <h3 class="convites-titulo">
              <i class="fa-solid fa-envelope"></i>
              Convites pendentes ({{ $convites->count() }})
            </h3>
            @foreach($convites as $convite)
              <div class="convite-card">
                <div class="convite-info">
                  <img src="{{ $convite->capa ? asset($convite->capa) : asset('images/loading.png') }}"
                       class="convite-capa" alt="">
                  <div>
                    <strong>{{ $convite->nome }}</strong>
                    <span>por {{ $convite->criador->name }}</span>
                  </div>
                </div>
                <div class="convite-acoes">
                  <form action="{{ route('projetos.aceitar', $convite) }}" method="POST" style="display:contents;">
                    @csrf
                    <button type="submit" class="btn-aceitar">
                      <i class="fa-solid fa-check"></i> Aceitar
                    </button>
                  </form>
                  <form action="{{ route('projetos.recusar', $convite) }}" method="POST" style="display:contents;">
                    @csrf
                    <button type="submit" class="btn-recusar">
                      <i class="fa-solid fa-xmark"></i> Recusar
                    </button>
                  </form>
                </div>
              </div>
            @endforeach
          </div>
        @endif

        {{-- Lista de projetos --}}
        <div class="projects-list">

<div id="resultadoVazio" class="empty-state project-empty-state" style="display:none;">
    <div class="project-empty-icon"><i class="fa-solid fa-diagram-project"></i></div>
    <h3>Nenhum projeto encontrado</h3>
    <p>Tente outro nome, tecnologia ou status para filtrar seus projetos.</p>
</div>

          @forelse($projetos as $projeto)

            {{-- Card inteiro clicável + menu de opções separado --}}
            <div class="project-card-wrapper">

              <a href="{{ route('projetos.show', $projeto) }}" class="project-card">
                <img src="{{ $projeto->capa ? asset($projeto->capa) : asset('images/loading.png') }}"
                     class="project-logo" alt="{{ $projeto->nome }}">

                <div class="project-info">
                  <div class="project-top">
                    <h3>{{ $projeto->nome }}</h3>
                    <span class="status {{ $projeto->status === 'Concluído' ? 'active' : 'pending' }}">
                      {{ $projeto->status }}
                    </span>
                  </div>

                  @if($projeto->criador)
                    <div class="project-owner-line">
                      <img src="{{ asset($projeto->criador->foto ?? 'images/default-user.png') }}" alt="">
                      <span>{{ $projeto->criador->name }}</span>
                      @if($projeto->criador?->isVerifiedProfessor())
                        <span class="teacher-verified-badge mini"><i class="fa-solid fa-circle-check"></i></span>
                      @endif
                    </div>
                  @endif

                  <p class="project-description">
                    {{ Str::limit(strip_tags($projeto->descricao), 180) }}
                  </p>

                  <div class="tech-list">
                    @foreach(($projeto->tecnologias ?? []) as $tech)
                      <span>{{ $tech }}</span>
                    @endforeach
                  </div>

                  <div class="project-footer">
                    <span><i class="fa-solid fa-users"></i> {{ $projeto->membros->count() }} membros</span>
                    <span><i class="fa-regular fa-calendar"></i> {{ $projeto->created_at->format('d/m/Y') }}</span>
                  </div>
                </div>
              </a>

              {{-- Menu de opções (fora do <a> para não conflitar com o clique) --}}
              @if($projeto->user_id === Auth::id())
                <div class="project-menu">
                  <button class="project-menu-btn" type="button" aria-label="Mais opções">⋯</button>
                  <div class="project-menu-dropdown">
                    <a href="{{ route('projetos.show', $projeto) }}" class="menu-item">
                      <i class="fa-solid fa-eye"></i> Ver projeto
                    </a>
                    <form action="{{ route('projetos.destroy', $projeto) }}" method="POST"
                          onsubmit="return confirm('Tem certeza que deseja excluir este projeto?')">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="menu-item menu-item-danger">
                        <i class="fa-solid fa-trash"></i> Excluir projeto
                      </button>
                    </form>
                  </div>
                </div>
              @endif

            </div>

          @empty
            <div class="empty-state project-empty-state">
              <div class="project-empty-icon"><i class="fa-solid fa-diagram-project"></i></div>
              <h3>Nenhum projeto encontrado</h3>
              <p>Crie seu primeiro projeto para organizar ideias, membros e tecnologias em um só lugar.</p>
              <a href="{{ route('projetoscad') }}" class="new-project-btn">
                <i class="fa-solid fa-plus"></i> Criar primeiro projeto
              </a>
            </div>
          @endforelse
        </div>

      </div>

      <div class="projects-right">
        <div class="filter-card">
          <h3>Filtros</h3>
          <input type="text" id="filtroNome" placeholder="Buscar projeto">
          <select id="filtroStatus">
            <option value="">Todos os status</option>
            <option>Planejamento</option>
            <option>Em desenvolvimento</option>
            <option>Concluído</option>
            <option>Arquivado</option>
          </select>
          <button type="button" onclick="aplicarFiltro()">Aplicar filtros</button>
        </div>

        <div class="summary-card">
          <h3>Resumo</h3>
          <div class="summary-item">
            <span>Projetos</span>
            <strong>{{ $resumo['total'] }}</strong>
          </div>
          <div class="summary-item">
            <span>Em andamento</span>
            <strong>{{ $resumo['em_andamento'] }}</strong>
          </div>
          <div class="summary-item">
            <span>Concluídos</span>
            <strong>{{ $resumo['concluidos'] }}</strong>
          </div>
          <div class="summary-item">
            <span>Arquivados</span>
            <strong>{{ $resumo['arquivados'] }}</strong>
          </div>
        </div>
      </div>

    </div>
  </div>

  <script src="{{ asset('js/projetos.js') }}"></script>
  <script>
    // Filtro client-side simples
function aplicarFiltro() {
    const nome = document.getElementById('filtroNome').value.toLowerCase().trim();
    const status = document
    .getElementById('filtroStatus')
    .value
    .trim()
    .toLowerCase();

    let encontrados = 0;

    document.querySelectorAll('.project-card-wrapper').forEach(wrapper => {

        const titulo =
            wrapper.querySelector('h3')?.textContent.toLowerCase() || '';

        const descricao =
            wrapper.querySelector('.project-description')?.textContent.toLowerCase() || '';

        const statusProjeto =
    wrapper.querySelector('.status')
        ?.textContent
        .trim()
        .toLowerCase() || '';

        const tecnologias = Array.from(
            wrapper.querySelectorAll('.tech-list span')
        )
        .map(t => t.textContent.toLowerCase())
        .join(' ');

        const correspondeNome =
            !nome ||
            titulo.includes(nome) ||
            descricao.includes(nome) ||
            tecnologias.includes(nome);

        const correspondeStatus =
            !status ||
            statusProjeto === status;

        const mostrar =
            correspondeNome &&
            correspondeStatus;

        wrapper.style.display = mostrar ? '' : 'none';

        if (mostrar) encontrados++;
    });

    const vazio = document.getElementById('resultadoVazio');

    if (vazio) {
        vazio.style.display = encontrados ? 'none' : 'block';
    }
}

    // Menu dropdown dos projetos
    document.querySelectorAll('.project-menu-btn').forEach(btn => {
        btn.addEventListener('click', e => {
            e.stopPropagation();
            const dropdown = btn.nextElementSibling;
            document.querySelectorAll('.project-menu-dropdown').forEach(d => {
                if (d !== dropdown) d.classList.remove('open');
            });
            dropdown.classList.toggle('open');
        });
    });
    document.addEventListener('click', () => {
        document.querySelectorAll('.project-menu-dropdown').forEach(d => d.classList.remove('open'));
    });

    document.getElementById('filtroNome')
    .addEventListener('input', aplicarFiltro);

document.getElementById('filtroStatus')
    .addEventListener('change', aplicarFiltro);
  </script>
  <script src="{{ asset('js/notifications.js') }}"></script>
</body>
</html>