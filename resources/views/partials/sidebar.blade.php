{{-- menu lateral das telas logadas --}}
<aside class="sidebar">
    <div class="sidebar-content">
        <ul>
            <li class="{{ request()->routeIs('home') ? 'active' : '' }}">
                <a href="{{ route('home') }}"><i class="fa-solid fa-house"></i><span>Home</span></a>
            </li>
            <li class="{{ request()->routeIs('perfil') ? 'active' : '' }}">
                <a href="{{ route('perfil') }}"><i class="fa-regular fa-user"></i><span>Perfil</span></a>
            </li>
            <li class="{{ request()->routeIs('conexoes') ? 'active' : '' }}">
                <a href="{{ route('conexoes') }}"><i class="fa-solid fa-user-group"></i><span>Conexões</span></a>
            </li>
            <li class="{{ request()->routeIs('projetos*') ? 'active' : '' }}">
                <a href="{{ route('projetos') }}"><i class="fa-regular fa-folder"></i><span>Projetos</span></a>
            </li>
            <li class="{{ request()->routeIs('config') ? 'active' : '' }}">
                <a href="{{ route('config') }}"><i class="fa-solid fa-gear"></i><span>Configurações</span></a>
            </li>
        </ul>
    </div>

    <div class="sidebar-bottom">
        <div class="sidebar-profile">
            <img src="{{ asset(Auth::user()->foto) }}" class="profile-pic">
            <div>
                <h4>{{ Auth::user()->name }}</h4>
                @if(Auth::user()->isVerifiedProfessor())
                    <span class="teacher-verified-badge compact"><i class="fa-solid fa-circle-check"></i> Professor verificado</span>
                @elseif(Auth::user()->curso)
                    <span>{{ Auth::user()->curso }}</span>
                @endif
            </div>
        </div>
        <form method="POST" action="/logout">
            @csrf
            <button class="logout" type="submit">
                <i class="fa-solid fa-arrow-right-from-bracket"></i> Sair
            </button>
        </form>
    </div>
</aside>
