<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Editar um Projeto - UniceHub</title>

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

        <form action="{{ route('projetos.store') }}" method="POST" enctype="multipart/form-data" id="formProjeto" class="project-layout">
            @csrf

         <div class="project-main">

    <div class="create-project-card">
        <h1>Criar Projeto</h1>

            <label for="nome">Nome do Projeto</label>
            <input id="nome" name="nome" type="text" maxlength="100" required>

            <label for="descricao">Descrição (Markdown)</label>
            <textarea
                id="descricao"
                name="descricao"
                maxlength="5000"
                placeholder="Use markdown como um README do GitHub..."
                required
            ></textarea>

            <div class="form-row">
                <div>
                    <label for="categoria">Categoria</label>
                    <select id="categoria" name="categoria">
                        <option value="Desenvolvimento Web">Desenvolvimento Web</option>
                        <option value="Desenvolvimento Mobile">Desenvolvimento Mobile</option>
                        <option value="Banco de Dados">Banco de Dados</option>
                        <option value="Inteligência Artificial">Inteligência Artificial</option>
                        <option value="Machine Learning">Machine Learning</option>
                        <option value="Deep Learning">Deep Learning</option>
                        <option value="Ciência de Dados">Ciência de Dados</option>
                        <option value="Big Data">Big Data</option>
                        <option value="Cloud Computing">Cloud Computing</option>
                        <option value="DevOps">DevOps</option>
                        <option value="Cibersegurança">Cibersegurança</option>
                        <option value="Redes de Computadores">Redes de Computadores</option>
                        <option value="Programação Backend">Programação Backend</option>
                        <option value="Programação Frontend">Programação Frontend</option>
                        <option value="Full Stack">Full Stack</option>
                        <option value="Engenharia de Software">Engenharia de Software</option>
                        <option value="Arquitetura de Sistemas">Arquitetura de Sistemas</option>
                        <option value="Internet das Coisas (IoT)">Internet das Coisas (IoT)</option>
                        <option value="Computação em Nuvem">Computação em Nuvem</option>
                        <option value="Blockchain">Blockchain</option>
                        <option value="Desenvolvimento de Jogos">Desenvolvimento de Jogos</option>
                        <option value="Realidade Virtual (VR)">Realidade Virtual (VR)</option>
                        <option value="Realidade Aumentada (AR)">Realidade Aumentada (AR)</option>
                        <option value="UX/UI Design">UX/UI Design</option>
                        <option value="Design de Interfaces">Design de Interfaces</option>
                        <option value="Automação de Processos">Automação de Processos</option>
                        <option value="Testes de Software">Testes de Software</option>
                        <option value="Quality Assurance (QA)">Quality Assurance (QA)</option>
                        <option value="Sistemas Embarcados">Sistemas Embarcados</option>
                        <option value="Robótica">Robótica</option>
                        <option value="Análise de Sistemas">Análise de Sistemas</option>
                        <option value="Computação Quântica">Computação Quântica</option>
                        <option value="Java">Java</option>
                        <option value="Python">Python</option>
                        <option value="C#">C#</option>
                        <option value="JavaScript">JavaScript</option>
                        <option value="React">React</option>
                        <option value="Node.js">Node.js</option>
                        <option value="Laravel">Laravel</option>
                        <option value="Spring Boot">Spring Boot</option>
                        <option value="Docker">Docker</option>
                        <option value="Kubernetes">Kubernetes</option>
                        <option value="MySQL">MySQL</option>
                        <option value="PostgreSQL">PostgreSQL</option>
                        <option value="MongoDB">MongoDB</option>
                        <option value="Git e GitHub">Git e GitHub</option>
                        <option value="Linux">Linux</option>
                        <option value="Figma">Figma</option>
                    </select>
                </div>

                <div>
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option>Em desenvolvimento</option>
                        <option>Concluído</option>
                        <option>Arquivado</option>
                    </select>
                </div>
            </div>

            <label>Tecnologias</label>
            <div class="tech-input-wrap">
                <div class="tech-tags" id="techTags"></div>
                <input
                    id="techInput"
                    type="text"
                    placeholder="Digite e pressione Enter"
                    class="tech-input"
                >
                <div id="techInputsContainer"></div>
            </div>

            <label for="repo_url">Link do repositório (opcional)</label>
            <input
                id="repo_url"
                name="repo_url"
                type="url"
                placeholder="https://github.com/seu-usuario/seu-repositorio"
            >

            <label for="capa">Imagem de Capa</label>
            <div class="upload-area">
                <i class="fa-regular fa-image"></i>
                <h4>Adicionar capa do projeto</h4>
                <p>PNG, JPG ou SVG (MÁX 5 MB)</p>
                <input id="capa" name="capa" type="file" accept="image/*">
            </div>

            <button type="submit">Publicar Projeto</button>
    </div>

</div> <!-- fecha project-main -->

<aside class="project-sidebar">

        <div class="preview-card">
            <h3>Pré-visualização</h3>
            <div class="preview-project">
                <img id="previewCapa" src="{{ asset('images/loading.png') }}">
                <h4 id="previewNome">Nome do projeto</h4>
                <div class="preview-markdown" id="previewMarkdown">Descrição do projeto...</div>
                <div class="preview-tags" id="previewTecnologias"></div>
                <a id="previewRepoLink" class="preview-repo-link" href="#" target="_blank" rel="noopener noreferrer" hidden></a>
            </div>
        </div>

        <section class="project-members">
            <div class="members-header">
                <h3>Membros do projeto</h3>
                <span class="members-count" id="membersCount">0 selecionado</span>
            </div>

            <input
                type="search"
                class="members-search"
                placeholder="Buscar pessoa..."
                aria-label="Buscar pessoa para adicionar ao projeto"
            >

            <p class="members-description">
                Convide pessoas que você segue ou que seguem você.
            </p>

            <div class="member-list">
                @foreach($pessoasDisponiveis as $pessoa)
                    <label class="member-card member-card-selectable" data-member-name="{{ strtolower($pessoa->name) }}" data-member-email="{{ strtolower($pessoa->email) }}">
                        <input type="checkbox" name="membros[]" value="{{ $pessoa->id }}" class="member-checkbox">
                        <img src="{{ asset($pessoa->foto) }}" alt="{{ $pessoa->name }}">
                        <div class="member-info">
                            <h4>{{ $pessoa->name }}</h4>
                            <span>{{ $pessoa->curso ?? 'Sem curso' }}</span>
                        </div>
                    </label>
                @endforeach
            </div>
        </section>

        <div class="summary-card">
            <h3>Resumo do Projeto</h3>
            <p><i class="fa-solid fa-user"></i> Criado por: {{ Auth::user()->name }}</p>
            <p><i class="fa-solid fa-circle-check"></i> Status: <span id="summaryStatus">Em desenvolvimento</span></p>
            <p><i class="fa-solid fa-calendar"></i> Data: {{ now()->format('d/m/Y') }}</p>
            <p><i class="fa-solid fa-code"></i> Tecnologia: <span id="summaryTecnologia">-</span></p>
        </div>

    </aside>

</form>

</div>

<script src="{{ asset('js/projetoscad.js') }}"></script>
  <script src="{{ asset('js/notifications.js') }}"></script>
</body>
</html>