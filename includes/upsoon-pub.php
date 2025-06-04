<?php

namespace UpSoon\Includes;

if (!defined('ABSPATH')) {
    exit;
}

class Pub
{
    public function init()
    {
        if (function_exists('wp_body_open')) {
            add_action('wp_body_open', [$this, 'display_banner']);
        } else {
            // Alternative pour versions WordPress plus anciennes (par exemple wp_head())
            add_action('wp_head', [$this, 'display_banner']);
        }
        add_action('wp_enqueue_scripts', [$this, 'enqueue_styles']);
    }

    public function display_banner()
    {
        if (get_option('upsoon_banner_enabled', 'Deactivated') !== 'Activated') {
            return;
        }

        $text = esc_html(get_option('upsoon_banner_text', 'Test'));
        $position = get_option('upsoon_banner_pos', 'Bottom Right');
        $bg_color = esc_attr(get_option('upsoon_banner_color', '#00008B'));
        $text_color = esc_attr(get_option('upsoon_banner_text_color', '#FFFFFF'));
        $border_radius = esc_attr(get_option('upsoon_banner_border_radius', '16px'));
        $font_size = esc_attr(get_option('upsoon_banner_font_size', '16px'));
        $z_index = esc_attr(get_option('upsoon_banner_z_index', '9999'));

        $positions = [
            'Top Left' => 'top: 20px; left: 20px;',
            'Top Right' => 'top: 20px; right: 20px;',
            'Bottom Left' => 'bottom: 20px; left: 20px;',
            'Bottom Right' => 'bottom: 20px; right: 20px;',
            'Top' => 'top: 20px; left: 50%; transform: translateX(-50%);',
            'Bottom' => 'bottom: 20px; left: 50%; transform: translateX(-50%);',
            'Left' => 'top: 50%; left: 20px; transform: translateY(-50%);',
            'Right' => 'top: 50%; right: 20px; transform: translateY(-50%);',
        ];
        $style_pos = $positions[$position] ?? $positions['Bottom Right'];

        // Affichage avec classes + styles inline personnalisés
        echo "<div id='upsoon-banner' class='upsoon-banner' style='
            background-color: {$bg_color};
            color: {$text_color};
            border-radius: {$border_radius};
            font-size: {$font_size};
            z-index: {$z_index};
            {$style_pos}
        '>{$text}</div>";
    }

    public function enqueue_styles()
    {
        if (get_option('upsoon_banner_enabled', 'Deactivated') !== 'Activated') {
            return;
        }

        $custom_path = UP_SOON_PLUGIN_DIR . 'public/assets/css/upsoon-banner-custom.css';
        $custom_url = UP_SOON_PLUGIN_URL . 'public/assets/css/upsoon-banner-custom.css';

        $default_path = UP_SOON_PLUGIN_DIR . 'public/assets/css/upsoon-banner.css';
        $default_url = UP_SOON_PLUGIN_URL . 'public/assets/css/upsoon-banner.css';

        if (file_exists($custom_path) && filesize($custom_path) > 0) {
            wp_enqueue_style('upsoon-banner-style', $custom_url, [], filemtime($custom_path));
        } elseif (file_exists($default_path)) {
            wp_enqueue_style('upsoon-banner-style', $default_url, [], filemtime($default_path));
        }
    }
}
