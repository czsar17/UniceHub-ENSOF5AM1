@php use Illuminate\Support\Facades\Auth; @endphp
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Configurações - UniceHub</title>
    <link rel="stylesheet" href="{{ asset('css/base.css') }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/config-extra.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="{{ route('theme.css') }}">
</head>
<body>

  <!-- HEADER -->
  @include('partials.header')

  <div class="main-container">

    <!-- SIDEBAR -->
    @include('partials.sidebar')

    <!-- CONTEÚDO PRINCIPAL -->
    <main class="cfg-main">

      <!-- Breadcrumb -->
      <div class="cfg-breadcrumb">
        <a href="{{ route('config') }}">Configurações</a>
        <i class="fa-solid fa-chevron-right"></i>
        <span id="configBreadcrumb">Informações pessoais</span>
      </div>

      <div class="cfg-page-title">
        <h1>Configurações</h1>
        <p>Gerencie suas preferências e configurações da conta.</p>
      </div>

      <div class="cfg-layout">

        <!-- Menu lateral -->
        <aside class="cfg-sidebar-menu">
          <div class="menu-group">
            <h3>Conta</h3>
            <button class="menu-item active" data-page="perfil" data-label="Informações pessoais">
              <i class="fa-regular fa-user"></i> Informações pessoais
            </button>
            <button class="menu-item" data-page="seguranca" data-label="Segurança">
              <i class="fa-solid fa-shield-halved"></i> Segurança
            </button>
            <button class="menu-item" data-page="notificacoes" data-label="Notificações">
              <i class="fa-regular fa-bell"></i> Notificações
            </button>
            <button class="menu-item" data-page="tipoPerfil" data-label="Tipo de perfil">
              <i class="fa-solid fa-user-tag"></i> Tipo de perfil
            </button>
          </div>

          @if(Auth::user()->is_admin)
          <div class="menu-group">
            <h3>Admin</h3>
            <button class="menu-item" data-page="personalizacaoAdm" data-label="Personalização ADM">
              <i class="fa-solid fa-palette"></i> Personalização
            </button>
            <button class="menu-item" data-page="usuariosAdm" data-label="Usuários ADM">
              <i class="fa-solid fa-users-gear"></i> Usuários
            </button>
            <button class="menu-item" data-page="professoresAdm" data-label="Aprovação de professores">
              <i class="fa-solid fa-user-check"></i> Professores
            </button>
          </div>
          @endif

          <div class="menu-group">
            <h3>Sistema</h3>
            <button class="menu-item" data-page="sobre" data-label="Sobre o UniceHub">
              <i class="fa-solid fa-circle-info"></i> Sobre o UniceHub
            </button>
            <button class="menu-item" data-page="privacidade" data-label="Política de privacidade">
              <i class="fa-solid fa-file-shield"></i> Política de privacidade
            </button>
          </div>
        </aside>

        <!-- Área de conteúdo rolável -->
        <div class="cfg-content-area">
          <div class="cfg-card" id="configContent"></div>
        </div>

      </div>
    </main>
  </div>

  <!-- Dados do usuário para o JS (sem expor dados sensíveis) -->
  <script>
    window.cfgUser = {
      name:           @json(Auth::user()->name),
      email:          @json(Auth::user()->email),
      cpf:            @json(Auth::user()->cpf),
      data_nascimento:@json(Auth::user()->data_nascimento),
      telefone:       @json(Auth::user()->telefone ?? ''),
      curso:          @json(Auth::user()->curso ?? ''),
      tipo:           @json(Auth::user()->tipo),
      isAdmin:        @json((bool) Auth::user()->is_admin),
      sobre_mim:      @json(Auth::user()->sobre_mim ?? ''),
      interesses_markdown: @json(Auth::user()->interesses_markdown ?? ''),
      tecnologias:    @json(Auth::user()->tecnologias ?? []),
      csrfToken:      @json(csrf_token()),
      updateUrl:      @json(route('perfil.atualizar')),
      passwordUrl:    @json(route('config.senha')),
      deleteAccountUrl: @json(route('config.conta.excluir')),
    };

    window.cfgAdmin = {
      isAdmin:        @json((bool) Auth::user()->is_admin),
      theme:          @json($systemTheme),
      usersUrl:       @json(route('admin.usuarios')),
      userUpdateUrl:  @json(route('admin.usuarios.atualizar')),
      professorRequestsUrl: @json(route('admin.professores')),
      professorReviewUrl:   @json(route('admin.professores.revisar')),
      themeSaveUrl:   @json(route('admin.tema.salvar')),
      themeResetUrl:  @json(route('admin.tema.restaurar')),
      themeCssUrl:    @json(route('theme.css')),
    };
  </script>
  <script src="{{ asset('js/config.js') }}"></script>
  <script src="{{ asset('js/notifications.js') }}"></script>
</body>
</html>