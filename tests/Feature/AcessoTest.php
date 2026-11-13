<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// testes de acesso: quem pode entrar em cada tela
class AcessoTest extends TestCase
{
    use RefreshDatabase;

    public function test_raiz_manda_pro_login(): void
    {
        $this->get('/')->assertRedirect(route('login'));
        $this->get('/login')->assertOk();
    }

    public function test_quem_nao_logou_nao_entra_na_home(): void
    {
        $this->get('/home')->assertRedirect(route('login'));
    }

    public function test_usuario_logado_ve_as_telas_principais(): void
    {
        $this->actingAs(User::factory()->create());

        foreach (['/home', '/perfil', '/conexoes', '/projetos', '/projetoscad', '/config', '/buscar?q=teste'] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_quem_nao_e_admin_leva_403_nas_rotas_de_admin(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => false]));

        $this->getJson('/config/admin/usuarios')->assertForbidden();
        $this->postJson('/config/admin/tema/restaurar')->assertForbidden();
    }

    public function test_admin_consegue_listar_usuarios(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        $this->getJson('/config/admin/usuarios')->assertOk()->assertJsonCount(1, 'usuarios');
    }

    public function test_professor_pendente_nao_consegue_logar(): void
    {
        User::factory()->create([
            'email' => 'prof@teste.com',
            'tipo' => 'professor',
            'approval_status' => 'pending',
        ]);

        $this->post('/login', ['email' => 'prof@teste.com', 'password' => 'password'])
            ->assertViewIs('auth.professor-pending');

        $this->assertGuest();
    }
}
