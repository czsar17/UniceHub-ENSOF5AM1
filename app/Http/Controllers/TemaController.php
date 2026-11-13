<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

// tema do sistema: o css gerado e a personalizacao feita pelo admin
class TemaController extends Controller
{
    public function css()
    {
        $theme = $this->systemTheme();
        $fontSize = max(13, min(20, (int) ($theme['font_size'] ?? 16)));
        $contrast = max(0, min(100, (int) ($theme['contrast'] ?? 50)));
        $contrastPercent = 85 + ($contrast * 0.3);
        $radius = match ($theme['layout_style'] ?? 'glass') {
            'solid' => '18px',
            'compact' => '12px',
            default => '28px',
        };
        $cardOpacity = ($theme['layout_style'] ?? 'glass') === 'solid' ? '1' : (($theme['layout_style'] ?? 'glass') === 'compact' ? '0.92' : '0.80');
        $shadow = !empty($theme['soft_shadows']) ? '0 10px 30px rgba(0, 0, 0, 0.08)' : 'none';
        $motion = (!empty($theme['smooth_animations']) && empty($theme['reduce_motion'])) ? '0.3s' : '0s';
        $borderStyle = $theme['border_style'] ?? 'solid';
        $border = $borderStyle === 'none' ? 'none' : "1px {$borderStyle} rgba(45, 106, 99, 0.22)";
        $logo = asset($theme['logo_path']);
        $background = asset($theme['background_path']);
        $authBackground = asset($theme['auth_background_path']);

        $css = <<<CSS
    :root {
        --cor-verde: {$theme['accent_color']};
        --cor-verde-escuro: {$theme['primary_color']};
        --cor-verde-btn: {$theme['accent_color']};
        --cor-verde-hover: {$theme['primary_color']};
        --cor-verde-claro: color-mix(in srgb, {$theme['accent_color']} 28%, white);
        --cor-texto: {$theme['text_color']};
        --auth-primary: {$theme['primary_color']};
        --auth-secondary: {$theme['secondary_color']};
        --auth-text: {$theme['text_color']};
        --auth-muted: {$theme['text_secondary_color']};
        --auth-input-bg: {$theme['input_background_color']};
        --font-principal: {$theme['font_family']};
        --font-titulos: {$theme['title_font_family']};
        --radius-card: {$radius};
        --shadow-card: {$shadow};
        --theme-card-opacity: {$cardOpacity};
        --theme-transition: {$motion};
        --theme-border: {$border};
        --theme-primary: {$theme['primary_color']};
        --theme-secondary: {$theme['secondary_color']};
        --theme-accent: {$theme['accent_color']};
        --theme-bg: {$theme['background_color']};
        --theme-section: {$theme['section_color']};
        --theme-text: {$theme['text_color']};
        --theme-text-muted: {$theme['text_secondary_color']};
        --theme-input-bg: {$theme['input_background_color']};
        font-size: {$fontSize}px;
    }
    body {
        background-color: {$theme['background_color']};
        background-repeat: no-repeat;
        background-size: cover;
        background-attachment: fixed;
        color: {$theme['text_color']};
        font-family: var(--font-principal);
        filter: contrast({$contrastPercent}%);
    }
    body:not(.registro):not(.auth-page) { background-image: url('{$background}'); }
    body.registro, body.auth-page { background-image: url('{$authBackground}'); }
    h1, h2, h3, .cfg-page-title h1, .cfg-breadcrumb a, .cfg-breadcrumb span, .post-content h2, .widget-card h3, .sidebar a, .menu-item { font-family: var(--font-titulos); }
    a, .menu-icon, .header-icons i, .notification, .search-box button, .search-box i, .menu-item i, .cfg-section-header h2 i, .sec-icon, .widget-card h3 i { color: var(--theme-primary) !important; }
    .header-logo, .logo-area img { content: url('{$logo}'); }
    .header, .main-header, .sidebar, .widget-card, .sidebar-card, .post-card, .projects-header, .empty-state, .cfg-card, .cfg-sidebar-menu, .profile-card, .profile-header, .profile-tabs, .profile-project-card, .profile-user-card, .comentarios-card, .connections-main, .connection-card, .request-card, .suggestion, .filter-card, .summary-card, .convite-card, .project-card, .project-mini-card, .project-sidebar, .create-project-card, .member-card, .project-members, .preview-card, .summary-card.projeto, .comment-box, .notification-panel, .notification-card, .cfg-adm-main, .cfg-adm-preview-side, .cfg-adm-card, .cfg-theme-card, .cfg-layout-card, .cfg-effect-item, .cfg-action-card, .cfg-bg-card, .cfg-notif-group, .cfg-profile-type, .cfg-info-box, .cfg-privacy-section, .search-result-card, .search-user-card, .preview-panel, .config-card, .security-section, .notification-section, .profile-section, .project-menu-dropdown {
        border: var(--theme-border);
        box-shadow: var(--shadow-card);
        transition-duration: var(--theme-transition);
    }
    .header, .main-header, .sidebar, .widget-card, .sidebar-card, .post-card, .projects-header, .empty-state, .cfg-card, .cfg-sidebar-menu, .profile-card, .profile-header, .profile-tabs, .profile-project-card, .profile-user-card, .comentarios-card, .connections-main, .connection-card, .request-card, .suggestion, .filter-card, .summary-card, .convite-card, .project-card, .project-mini-card, .project-sidebar, .create-project-card, .member-card, .project-members, .preview-card, .summary-card.projeto, .comment-box, .notification-panel, .notification-card, .cfg-adm-main, .cfg-adm-preview-side, .cfg-adm-card, .cfg-theme-card, .cfg-layout-card, .cfg-effect-item, .cfg-action-card, .cfg-bg-card, .cfg-notif-group, .cfg-profile-type, .cfg-info-box, .cfg-privacy-section, .search-result-card, .search-user-card, .preview-panel, .connections-tabs, .connections-list, .connections-top, .config-card, .security-section, .notification-section, .profile-section, .project-menu-dropdown {
        background: var(--theme-section) !important;
        background-color: var(--theme-section) !important;
    }
    body.auth-page .login-card, body.registro .register-card, body.registro .user-selector {
        background-color: color-mix(in srgb, var(--theme-section) 88%, transparent);
        color: var(--theme-text);
    }
    body.registro .register-card, body.registro .user-selector {
        border: 1px solid rgba(255,255,255,.45);
    }
    .menu-item.active, .sidebar li.active, .sidebar .active, .sidebar a.active, .nav-link.active, .cfg-profile-type.active-type, .connections-tabs .active {
        background-color: color-mix(in srgb, var(--theme-accent) 18%, var(--theme-section));
    }
    .sidebar li, .sidebar a, .menu-item, .sidebar-profile h4, .projects-header h1, .empty-state h3, .connections-header h1, .connections-top h2, .card-header h3, .projects-header h1, .page-header h1, .cfg-page-title h1, .cfg-breadcrumb a, .cfg-breadcrumb span, .profile-info h1, .profile-info h2, .profile-card h2, .profile-card h3, .profile-card h4, .profile-tabs button, .filter-card h3, .summary-card h3, .summary-item, .project-card h3, .project-top h3, .create-project-card h1, .preview-card h3, .preview-card h4, .member-card, .cfg-card, .cfg-card h2, .cfg-card h3, .cfg-card h4, .cfg-card strong, .cfg-sidebar-menu .menu-group h3, .cfg-label, .cfg-security-left strong, .cfg-notif-info strong, .cfg-theme-card strong, .cfg-layout-card strong, .notification-card h4, .search-result-card h4, .search-user-card h4 {
        color: var(--theme-text) !important;
    }
    .sidebar-profile span, .projects-header p, .empty-state p, .connections-header p, .connections-top span, .projects-header p, .page-header p, .cfg-page-title p, .cfg-breadcrumb i, .profile-stats, .profile-card p, .profile-card label, .profile-card span, .section-title-row span, .project-card p, .project-description, .project-footer, .create-project-card label, .preview-card p, .cfg-card p, .cfg-card span, .cfg-card small, .cfg-field-hint, .cfg-security-left span, .cfg-notif-info span, .cfg-theme-card small, .cfg-layout-card small, .notification-card span, .search-result-card p, .search-user-card span {
        color: var(--theme-text-muted) !important;
    }
    .widget-card, .sidebar-card, .post-card, .projects-header, .empty-state, .project-card, .profile-card, .profile-header, .profile-tabs, .filter-card, .summary-card, .cfg-card, .connections-main {
        backdrop-filter: blur(12px);
    }
    button, .cfg-btn-primary, .ver-projeto-btn, .conectar-btn, .connect-btn, .btn-primary, .submit-btn, .register-btn { border-color: transparent; }
    .cfg-btn-primary, .ver-projeto-btn, .conectar-btn, .connect-btn, .suggestion button, .project-mini-card button, .accept-btn, .new-project-btn, .btn-primary, .submit-btn { background-color: var(--theme-accent); color: #fff; }
    input, textarea, select, .cfg-input, .cfg-select, .cfg-readonly-field, .cfg-readonly-badge, .cfg-eye-btn, .profile-field, .nome-input, .curso-input, .search-box input, .connections-search input, .members-search, .filter-card input, .filter-card select, .create-project-card input, .create-project-card textarea, .create-project-card select {
        background-color: var(--theme-input-bg) !important;
        border-color: color-mix(in srgb, var(--theme-primary) 24%, transparent) !important;
        color: var(--theme-text) !important;
    }
    .search-box {
        background: transparent !important;
        border: none !important;
        box-shadow: none !important;
        border-radius: 999px !important;
    }
    .search-box input {
        border-radius: 999px !important;
    }
    .search-box button {
        border-radius: 999px !important;
    }
    input::placeholder, textarea::placeholder, .search-box input::placeholder, .cfg-input::placeholder {
        color: color-mix(in srgb, var(--theme-text-muted) 72%, transparent) !important;
    }
    input[type="date"]::-webkit-calendar-picker-indicator { filter: none; }
    .tags span, .connections-count, .search-result-tags span, .project-tag, .tech-tag {
        background-color: color-mix(in srgb, var(--theme-accent) 18%, var(--theme-section));
        color: var(--theme-primary);
    }
    .empty-connections i, .search-empty-state i { color: color-mix(in srgb, var(--theme-primary) 32%, var(--theme-section)); }
    .teacher-verified-badge {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        width: fit-content !important;
        min-height: 24px !important;
        margin: 0 !important;
        padding: 4px 9px !important;
        border-radius: 999px !important;
        background: color-mix(in srgb, var(--theme-primary) 10%, var(--theme-section)) !important;
        color: var(--theme-primary) !important;
        border: 1px solid color-mix(in srgb, var(--theme-primary) 24%, transparent) !important;
        font-size: 11px !important;
        font-weight: 800 !important;
        line-height: 1 !important;
        box-shadow: none !important;
    }
    .teacher-verified-badge i { color: var(--theme-accent) !important; font-size: 12px !important; }
    .teacher-verified-badge.compact, .teacher-verified-badge.mini {
        width: 20px !important;
        height: 20px !important;
        min-width: 20px !important;
        min-height: 20px !important;
        padding: 0 !important;
        overflow: hidden !important;
        font-size: 0 !important;
        white-space: nowrap !important;
        background: var(--theme-section) !important;
    }
    .teacher-verified-badge.profile {
        min-height: 28px !important;
        margin: 6px 0 8px !important;
        padding: 6px 10px !important;
        background: color-mix(in srgb, var(--theme-accent) 14%, var(--theme-section)) !important;
        font-size: 12px !important;
    }
    CSS;

        $layoutStyle = $theme['layout_style'] ?? 'glass';
        if ($layoutStyle === 'solid') {
            $css .= "
    /* Layout: Painel solido */
    .header, .main-header, .sidebar, .widget-card, .sidebar-card, .post-card, .projects-header, .empty-state, .project-card, .filter-card, .summary-card, .cfg-card, .cfg-sidebar-menu, .profile-card, .profile-header, .profile-tabs, .connections-main, .search-result-card, .search-user-card {
        backdrop-filter: none !important;
        border-radius: 16px !important;
        box-shadow: 0 8px 18px rgba(0, 0, 0, .06) !important;
    }
    .main-container { gap: 28px; }
    .sidebar { width: 230px; }
    .sidebar li { border-radius: 12px; margin-bottom: 8px; }
    .profile-body { grid-template-columns: minmax(0, 1fr) 340px; align-items: start; }
    .profile-tabs { justify-content: flex-start; gap: 8px; }
    .profile-tabs button { border-radius: 10px; }
    .projects-container, .connections-layout { gap: 24px; }
    .cfg-layout { grid-template-columns: 220px minmax(0, 1fr); gap: 20px; }
    .cfg-card { padding: 26px !important; }
    ";
        }

        if ($layoutStyle === 'compact') {
            $css .= "
    /* Layout: Compacto */
    .header, .main-header { height: 64px !important; min-height: 64px; border-radius: 18px !important; padding-left: 22px !important; padding-right: 22px !important; }
    .search-box input { height: 44px !important; }
    .main-container { gap: 14px; padding: 94px 14px 14px !important; }
    .sidebar { width: 196px; padding: 12px !important; border-radius: 16px !important; }
    .sidebar li { height: 46px; padding: 0 12px; border-radius: 12px; margin-bottom: 6px; font-size: 15px; }
    .sidebar-profile { padding: 10px !important; border-radius: 14px !important; }
    .logout { min-height: 42px; border-radius: 12px !important; }
    .projects-header, .empty-state, .post-card, .project-card, .filter-card, .summary-card, .cfg-card, .cfg-sidebar-menu, .profile-card, .profile-header, .profile-tabs, .search-result-card, .search-user-card {
        padding: 16px !important;
        border-radius: 12px !important;
    }
    .profile-body { grid-template-columns: 1fr; gap: 12px; }
    .profile-tabs { padding: 8px !important; gap: 6px; }
    .profile-tabs button { min-height: 36px; border-radius: 9px; }
    .cfg-layout { grid-template-columns: 190px minmax(0, 1fr); gap: 14px; }
    .cfg-sidebar-menu { width: 190px; }
    .cfg-sidebar-menu .menu-group { margin-bottom: 14px; }
    .cfg-sidebar-menu .menu-item { min-height: 40px; padding: 9px 10px; }
    .cfg-section-header { padding-bottom: 12px; margin-bottom: 14px; }
    .project-logo { width: 64px !important; height: 64px !important; min-width: 64px !important; }
    ";
        }

        if (!empty($theme['high_contrast'])) {
            $css .= "
    body { filter: contrast(120%); }
    ";
        }
        if (!empty($theme['reduce_motion'])) {
            $css .= "
    *, *::before, *::after { animation-duration: 0.01ms !important; transition-duration: 0.01ms !important; }
    ";
        }

        return response($css, 200)->header('Content-Type', 'text/css');
    }

    public function salvar(Request $request)
    {
        $data = $request->validate([
            'primary_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'secondary_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'accent_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'background_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'section_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'text_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'text_secondary_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'input_background_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'font_family' => [
                'required',
                Rule::in([
                    'Inter, Segoe UI, Arial, sans-serif',
                    'Poppins, Segoe UI, Arial, sans-serif',
                    'Arial, sans-serif',
                    'Georgia, serif',
                ]),
            ],
            'font_size' => 'required|integer|min:13|max:20',
            'title_font_family' => [
                'required',
                Rule::in([
                    'Inter, Segoe UI, Arial, sans-serif',
                    'Poppins, Segoe UI, Arial, sans-serif',
                    'Arial, sans-serif',
                    'Georgia, serif',
                ]),
            ],
            'layout_style' => ['required', Rule::in(['glass', 'solid', 'compact'])],
            'border_style' => ['required', Rule::in(['solid', 'dashed', 'dotted', 'none'])],
            'contrast' => 'required|integer|min:0|max:100',
            'soft_shadows' => 'nullable|boolean',
            'smooth_animations' => 'nullable|boolean',
            'high_contrast' => 'nullable|boolean',
            'reduce_motion' => 'nullable|boolean',
            'background_path' => 'nullable|string|max:255',
            'auth_background_path' => 'nullable|string|max:255',
            'logo' => 'nullable|file|mimes:png,jpg,jpeg,svg,webp|max:5120',
            'background' => 'nullable|image|max:5120',
            'auth_background' => 'nullable|image|max:5120',
        ]);

        $theme = $this->systemTheme();
        $theme = array_merge($theme, collect($data)->except(['logo', 'background', 'auth_background', 'background_path', 'auth_background_path'])->all());

        foreach (['background_path', 'auth_background_path'] as $pathKey) {
            $submittedPath = ltrim((string) $request->input($pathKey, ''), '/');
            if ($submittedPath !== '' && (str_starts_with($submittedPath, 'images/themes/') || str_starts_with($submittedPath, 'images/admin/') || in_array($submittedPath, ['images/bg-home.png', 'images/bgg-image.png'], true))) {
                $theme[$pathKey] = $submittedPath;
            }
        }

        foreach (['soft_shadows', 'smooth_animations', 'high_contrast', 'reduce_motion'] as $flag) {
            $theme[$flag] = $request->boolean($flag);
        }

        $dir = public_path('images/admin');
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        foreach ([
            'logo' => 'logo_path',
            'background' => 'background_path',
            'auth_background' => 'auth_background_path',
        ] as $input => $key) {
            if ($request->hasFile($input)) {
                $file = $request->file($input);
                $name = $input . '-' . time() . '.' . $file->getClientOriginalExtension();
                $file->move($dir, $name);
                $theme[$key] = 'images/admin/' . $name;
            }
        }

        DB::table('system_settings')->updateOrInsert(
            ['key' => 'theme'],
            ['value' => json_encode($theme), 'updated_at' => now(), 'created_at' => now()]
        );

        return response()->json([
            'message' => 'Personalização salva com sucesso.',
            'theme' => $theme,
        ]);
    }

    public function restaurar()
    {
        $theme = $this->defaultTheme();

        DB::table('system_settings')->updateOrInsert(
            ['key' => 'theme'],
            ['value' => json_encode($theme), 'updated_at' => now(), 'created_at' => now()]
        );

        return response()->json([
            'message' => 'Tema restaurado com sucesso.',
            'theme' => $theme,
        ]);
    }

    private function defaultTheme(): array
    {
        return [
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
            'high_contrast' => false,
            'reduce_motion' => false,
            'logo_path' => 'images/LOGOUNICEHUB-removebg-preview.png',
            'background_path' => 'images/themes/image-78.png',
            'auth_background_path' => 'images/themes/image-78.png',
        ];
    }

    private function systemTheme(): array
    {
        if (!Schema::hasTable('system_settings')) {
            return $this->defaultTheme();
        }

        $value = DB::table('system_settings')->where('key', 'theme')->value('value');
        $theme = is_string($value) ? json_decode($value, true) : [];

        return array_merge($this->defaultTheme(), is_array($theme) ? $theme : []);
    }
}
