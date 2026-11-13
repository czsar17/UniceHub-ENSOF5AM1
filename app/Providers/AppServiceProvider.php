<?php

namespace App\Providers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // manda o tema pra todas as telas do sistema
        $telas = ['auth.*', 'partials.*', 'projetos.*', 'home', 'busca', 'perfil', 'conexoes', 'config'];

        View::composer($telas, function ($view) {
            $defaultTheme = [
                'primary_color' => '#2D6A63',
                'secondary_color' => '#FF7A1A',
                'accent_color' => '#73B98F',
                'background_color' => '#F4FBF8',
                'section_color' => '#FFFFFF',
                'text_color' => '#21433D',
                'text_secondary_color' => '#6D7D78',
                'input_background_color' => '#F8FAFC',
                'font_family' => 'Inter, Segoe UI, Arial, sans-serif',
                'font_size' => '16',
                'title_font_family' => 'Inter, Segoe UI, Arial, sans-serif',
                'layout_style' => 'glass',
                'border_style' => 'solid',
                'contrast' => '50',
                'soft_shadows' => true,
                'smooth_animations' => true,
                'gradients' => false,
                'high_contrast' => false,
                'reduce_motion' => false,
                'logo_path' => 'images/LOGOUNICEHUB-removebg-preview.png',
                'background_path' => 'images/themes/image-78.png',
                'auth_background_path' => 'images/themes/image-78.png',
            ];

            // once() faz a consulta no banco so uma vez por pagina, mesmo com os partials
            $theme = once(function () use ($defaultTheme) {
                if (!Schema::hasTable('system_settings')) {
                    return $defaultTheme;
                }

                $value = DB::table('system_settings')->where('key', 'theme')->value('value');
                $decoded = is_string($value) ? json_decode($value, true) : [];

                return array_merge($defaultTheme, is_array($decoded) ? $decoded : []);
            });

            $view->with('systemTheme', $theme);
        });
    }
}
