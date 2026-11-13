<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ConexaoController;
use App\Http\Controllers\ConfigController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\ProjetoController;
use App\Http\Controllers\TemaController;
use Illuminate\Support\Facades\Route;

// ---------- rotas abertas (sem login) ----------

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', function () {
    return view('auth.login');
})->name('login');
Route::post('/login', [AuthController::class, 'login']);

Route::get('/registro', function () {
    return view('auth.registro');
})->name('registro');
Route::post('/registro', [AuthController::class, 'register']);

Route::post('/logout', [AuthController::class, 'logout']);

// esqueci a senha (codigo por email)
Route::get('/esqueci-senha', [AuthController::class, 'forgotPasswordForm'])->name('esqueci-senha');
Route::post('/esqueci-senha/enviar', [AuthController::class, 'sendPasswordResetCode'])->name('senha.enviar-codigo');
Route::post('/esqueci-senha/validar', [AuthController::class, 'verifyPasswordResetCode'])->name('senha.validar-codigo');
Route::post('/esqueci-senha/redefinir', [AuthController::class, 'resetPasswordWithCode'])->name('senha.redefinir');

// css do tema (a tela de login tambem usa)
Route::get('/theme.css', [TemaController::class, 'css'])->name('theme.css');

// ---------- rotas que precisam de login ----------

Route::middleware('auth')->group(function () {

    // home, busca e notificacoes
    Route::get('/home', [HomeController::class, 'home'])->name('home');
    Route::get('/buscar', [HomeController::class, 'buscar'])->name('buscar');
    Route::get('/notificacoes/header', [HomeController::class, 'notificacoesHeader'])->name('notificacoes.header');

    // perfil
    Route::get('/perfil', [PerfilController::class, 'perfil'])->name('perfil');
    Route::post('/perfil/atualizar', [PerfilController::class, 'atualizarPerfil'])->name('perfil.atualizar');
    Route::get('/usuarios/{user}', [PerfilController::class, 'visualizarUsuario'])->name('usuarios.show');

    // conexoes
    Route::get('/conexoes', [ConexaoController::class, 'conexoes'])->name('conexoes');
    Route::post('/seguir/{id}', [ConexaoController::class, 'seguir'])->name('seguir.enviar');
    Route::post('/seguir/aceitar/{id}', [ConexaoController::class, 'aceitarSeguidor'])->name('seguir.aceitar');
    Route::post('/seguir/recusar/{id}', [ConexaoController::class, 'recusarSeguidor'])->name('seguir.recusar');
    Route::post('/bloquear/{id}', [ConexaoController::class, 'bloquear'])->name('usuario.bloquear');

    // projetos
    Route::get('/projetos', [ProjetoController::class, 'index'])->name('projetos');
    Route::get('/projetoscad', [ProjetoController::class, 'create'])->name('projetoscad');
    Route::post('/projetos', [ProjetoController::class, 'store'])->name('projetos.store');
    Route::get('/projetos/{projeto}', [ProjetoController::class, 'show'])->name('projetos.show');
    Route::get('/projetos/{projeto}/editar', [ProjetoController::class, 'edit'])->name('projetos.edit');
    Route::put('/projetos/{projeto}', [ProjetoController::class, 'update'])->name('projetos.update');
    Route::delete('/projetos/{projeto}', [ProjetoController::class, 'destroy'])->name('projetos.destroy');
    Route::delete('/projetos/{projeto}/sair', [ProjetoController::class, 'sairProjeto'])->name('projetos.sair');

    // convites de projeto
    Route::post('/projetos/{projeto}/convidar', [ProjetoController::class, 'convidar'])->name('projetos.convidar');
    Route::post('/projetos/{projeto}/aceitar', [ProjetoController::class, 'aceitarConvite'])->name('projetos.aceitar');
    Route::post('/projetos/{projeto}/recusar', [ProjetoController::class, 'recusarConvite'])->name('projetos.recusar');

    // curtidas e comentarios
    Route::post('/projetos/{projeto}/curtir', [ProjetoController::class, 'curtir'])->name('projetos.curtir');
    Route::post('/projetos/{projeto}/comentar', [ProjetoController::class, 'comentar'])->name('projetos.comentar');
    Route::get('/projetos/{projeto}/comentarios', [ProjetoController::class, 'comentarios']);
    Route::get('/api/projetos/{projeto}/comentarios', [ProjetoController::class, 'listarComentarios']);
    Route::delete('/comentarios/{comentario}', [ProjetoController::class, 'excluirComentario'])->name('comentarios.excluir');

    // configuracoes da conta
    Route::get('/config', [ConfigController::class, 'config'])->name('config');
    Route::post('/config/senha', [ConfigController::class, 'trocarSenha'])->name('config.senha');
    Route::delete('/config/conta', [ConfigController::class, 'excluirConta'])->name('config.conta.excluir');

    // area do admin
    Route::middleware('admin')->prefix('config/admin')->group(function () {
        Route::get('/usuarios', [AdminController::class, 'usuarios'])->name('admin.usuarios');
        Route::post('/usuarios', [AdminController::class, 'atualizarUsuario'])->name('admin.usuarios.atualizar');
        Route::get('/professores', [AdminController::class, 'professoresPendentes'])->name('admin.professores');
        Route::post('/professores', [AdminController::class, 'revisarProfessor'])->name('admin.professores.revisar');
        Route::post('/tema', [TemaController::class, 'salvar'])->name('admin.tema.salvar');
        Route::post('/tema/restaurar', [TemaController::class, 'restaurar'])->name('admin.tema.restaurar');
    });
});
