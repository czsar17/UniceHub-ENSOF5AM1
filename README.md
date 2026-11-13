<p align="center">
  <img src="docs/logo.jpeg" width="200"/>
</p>

# UniceHub

O UniceHub é uma rede acadêmica inspirada no LinkedIn e no GitHub. A ideia é ajudar alunos e professores a se conectarem, mostrarem o que sabem e trabalharem juntos em projetos dentro da faculdade.

Projeto feito em equipe na disciplina de Engenharia de Software.

## Funcionalidades

- **Cadastro e login** de alunos e professores
  - cadastro de professor passa por aprovação de um administrador
  - recuperação de senha com código de 6 dígitos enviado por e-mail
- **Perfil** com foto, curso, "sobre mim", tecnologias e atividades recentes
- **Conexões**: seguir, aceitar, recusar e bloquear usuários
- **Projetos**: criar, editar, convidar membros, curtir e comentar
- **Busca** de pessoas e projetos
- **Notificações** no cabeçalho (conexões, convites, curtidas e comentários)
- **Área do administrador**
  - dar ou tirar permissão de admin
  - aprovar ou negar professores
  - personalizar o tema do sistema (cores, fontes, logo e fundo)

## Tecnologias

- **Back-end:** PHP 8.3 com Laravel 13
- **Front-end:** Blade, HTML, CSS e JavaScript puro
- **Banco de dados:** MySQL em produção e SQLite pra rodar local
- **Testes:** PHPUnit
- **Deploy:** servidor Ubuntu na AWS com HTTPS (Let's Encrypt)

## Como rodar no seu computador

Precisa ter PHP 8.3+ e o [Composer](https://getcomposer.org/) instalados.

```bash
git clone https://github.com/czsar17/UniceHub-ENSOF5AM1.git
cd UniceHub-ENSOF5AM1
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
php artisan serve
```

Depois é só abrir http://localhost:8000.

> O primeiro usuário cadastrado vira administrador automaticamente.

O `.env.example` já vem configurado com SQLite, então não precisa instalar banco nenhum. Pra usar MySQL, troque as variáveis `DB_*` no `.env`. As senhas ficam só no `.env`, que não vai pro git.

Por padrão os e-mails (como o código de recuperação de senha) não são enviados de verdade, eles vão pro arquivo `storage/logs/laravel.log`.

## Testes

```bash
php artisan test
```

## Estrutura do projeto

```
app/
  Http/
    Controllers/
      AuthController.php      login, cadastro, logout e recuperação de senha
      HomeController.php      feed, busca e notificações
      PerfilController.php    perfil próprio e de outros usuários
      ConexaoController.php   seguir, aceitar, recusar e bloquear
      ProjetoController.php   projetos, membros, curtidas e comentários
      ConfigController.php    trocar senha e excluir conta
      AdminController.php     usuários admin e aprovação de professores
      TemaController.php      tema do sistema (css gerado + personalização)
    Middleware/
      SomenteAdmin.php        bloqueia quem não é admin
  Models/                     User, Projeto, Follower, ComentarioProjeto, Atividade
database/migrations/          estrutura do banco
public/css, public/js         estilos e scripts de cada tela
resources/views/
  auth/                       login, cadastro, esqueci a senha
  partials/                   cabeçalho e menu lateral (usados em todas as telas)
  projetos/                   lista, cadastro/edição e visualização de projeto
  home, perfil, conexoes, busca, config
routes/web.php                todas as rotas, separadas em abertas, logadas e admin
tests/Feature/                testes de acesso e de perfil
```

## Equipe

- César Augusto Rodrigues Santos (Scrum Master)
- Thiago Henrikeh Evaristo de Faria (Desenvolvedor)
- João Pedro Frazão Silva (Desenvolvedor)
- Samuel Monteiro Nóbrega (Analista de Requisitos)

### Orientadores

- Hércules Júnior
- Romulo Riberiro
