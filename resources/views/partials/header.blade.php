{{-- cabecalho das telas logadas --}}
<header class="header">
    <div class="header-left">
        <i class="fa-solid fa-bars menu-icon"></i>
        <img src="{{ asset($systemTheme['logo_path'] ?? 'images/LOGOUNICEHUB-removebg-preview.png') }}" class="header-logo">
    </div>

    <form class="search-box" action="{{ route('buscar') }}" method="GET">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Pesquisar pessoas e projetos...">
        <button type="submit" aria-label="Pesquisar"><i class="fa-solid fa-magnifying-glass"></i></button>
    </form>

    <div class="header-icons">
        <i class="fa-regular fa-bell notification"></i>
        <div class="header-profile">
            <img src="{{ asset(Auth::user()->foto) }}" class="profile-pic">
        </div>
    </div>
</header>
