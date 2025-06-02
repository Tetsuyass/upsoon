<?php

namespace UpSoon\Admin;

if (!defined('ABSPATH')) {
    exit;
}

class Admin
{
    public function init() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('admin_init', array($this, 'maybe_save_custom_css'));
        add_action('admin_notices', array($this, 'settings_saved_notice'));
    }

    public function settings_saved_notice() {
        if (isset($_GET['settings-updated']) && $_GET['settings-updated'] === 'true' &&
            isset($_GET['page']) && strpos($_GET['page'], 'upsoon-') === 0) {
            add_settings_error(
                'upsoon_settings',
                'settings_updated',
                __('Settings saved successfully!', 'upsoon'),
                'updated'
            );
        }
    }

    public function add_admin_menu() {
        add_menu_page(
            __('UpSoon Settings', 'upsoon'),
            __('UpSoon', 'upsoon'),
            'manage_options',
            'upsoon-settings',
            array($this, 'render_settings_page'),
            'dashicons-megaphone',
            120
        );

        add_submenu_page('upsoon-settings', __('Settings', 'upsoon'), __('Settings', 'upsoon'), 'manage_options', 'upsoon-settings', array($this, 'render_settings_page'));
        add_submenu_page('upsoon-settings', __('Customization', 'upsoon'), __('Customization', 'upsoon'), 'manage_options', 'upsoon-perso', array($this, 'render_perso_page'));
        add_submenu_page('upsoon-settings', __('History', 'upsoon'), __('History', 'upsoon'), 'manage_options', 'upsoon-history', array($this, 'render_history_page'));
        add_submenu_page('upsoon-settings', __('Advanced Settings', 'upsoon'), __('Advanced', 'upsoon'), 'manage_options', 'upsoon-advanced', array($this, 'render_advanced_page'));
    }

    public function register_settings() {
        register_setting('upsoon_settings', 'upsoon_banner_enabled');
        register_setting('upsoon_settings', 'upsoon_banner_text', array('sanitize_callback' => array($this, 'sanitize_banner_text')));
        register_setting('upsoon_perso', 'upsoon_banner_pos');
        register_setting('upsoon_perso', 'upsoon_banner_color');
        register_setting('upsoon_perso', 'upsoon_banner_text_color');
        register_setting('upsoon_advanced', 'upsoon_banner_border_radius', array('sanitize_callback' => array($this, 'sanitize_banner_radius')));
        register_setting('upsoon_advanced', 'upsoon_banner_font_size', array('sanitize_callback' => array($this, 'sanitize_banner_font_size')));
        register_setting('upsoon_advanced', 'upsoon_banner_z_index', array('sanitize_callback' => array($this, 'sanitize_banner_z_index')));
        register_setting('upsoon_perso', 'upsoon_banner_text', array('sanitize_callback' => array($this, 'sanitize_banner_text')));

        add_settings_section('upsoon_section_base', __('Base Settings', 'upsoon'), array($this, 'render_section_base'), 'upsoon_settings');
        add_settings_section('upsoon_section_base', __('Customization Settings', 'upsoon'), array($this, 'render_section_base'), 'upsoon_perso');
        add_settings_section('upsoon_section_advanced', __('Advanced Settings', 'upsoon'), array($this, 'render_section_advanced'), 'upsoon_advanced');

        add_settings_field('upsoon_banner_enabled', __('Enable Banner', 'upsoon'), array($this, 'render_banner_enabled_field'), 'upsoon_settings', 'upsoon_section_base');
        add_settings_field('upsoon_banner_text', __('Banner Text', 'upsoon'), array($this, 'render_banner_text_field'), 'upsoon_perso', 'upsoon_section_base');
        add_settings_field('upsoon_banner_text', __('Banner Text', 'upsoon'), array($this, 'render_banner_text_field'), 'upsoon_settings', 'upsoon_section_base');
        add_settings_field('upsoon_banner_pos', __('Banner Position', 'upsoon'), array($this, 'render_banner_pos_field'), 'upsoon_perso', 'upsoon_section_base');
        add_settings_field('upsoon_banner_color', __('Banner Color', 'upsoon'), array($this, 'render_banner_color_field'), 'upsoon_perso', 'upsoon_section_base');
        add_settings_field('upsoon_banner_text_color', __('Banner Text Color', 'upsoon'), array($this, 'render_banner_text_color_field'), 'upsoon_perso', 'upsoon_section_base');
        add_settings_field('upsoon_banner_border_radius', __('Banner Border Radius', 'upsoon'), array($this, 'render_banner_border_radius_field'), 'upsoon_advanced', 'upsoon_section_advanced');
        add_settings_field('upsoon_banner_font_size', __('Banner Font Size', 'upsoon'), array($this, 'render_banner_font_size_field'), 'upsoon_advanced', 'upsoon_section_advanced');
        add_settings_field('upsoon_banner_z_index', __('Banner Z Index', 'upsoon'), array($this, 'render_banner_z_index_field'), 'upsoon_advanced', 'upsoon_section_advanced');
        add_settings_field('upsoon_banner_custom_css', __('Banner Custom CSS', 'upsoon'), array($this, 'render_banner_custom_css_field'), 'upsoon_advanced', 'upsoon_section_advanced');
    }

    public function enqueue_admin_scripts($hook) {
        if (strpos($hook, 'upsoon-') !== false) {
            wp_enqueue_style('upsoon-admin', UP_SOON_PLUGIN_URL . 'admin/css/upsoon-admin.css', array(), UP_SOON_VERSION);
            wp_enqueue_script('upsoon-admin', UP_SOON_PLUGIN_URL . 'admin/js/upsoon-admin.js', array(), UP_SOON_VERSION, true);
        }
    }

    public function render_section_base() {
        echo '<p>' . __('Base display settings for the banner', 'upsoon') . '</p>';
    }

    public function render_section_advanced() {
        echo '<p>' . __('Advanced display settings for the banner', 'upsoon') . '</p>';
    }

    public function render_banner_enabled_field() {
        $value = get_option('upsoon_banner_enabled', 'Deactivated');
        echo '<label><input type="radio" name="upsoon_banner_enabled" value="Activated"' . checked($value, 'Activated', false) . '> ' . __('Activated', 'upsoon') . '</label><br>';
        echo '<label><input type="radio" name="upsoon_banner_enabled" value="Deactivated"' . checked($value, 'Deactivated', false) . '> ' . __('Deactivated', 'upsoon') . '</label>';
        echo '<p class="description">' . __('Enable or Disable Banner.', 'upsoon') . '</p>';
    }

    public function render_banner_text_field() {
        $value = get_option('upsoon_banner_text', 'Test');
        echo '<input type="text" name="upsoon_banner_text" value="' . esc_attr($value) . '" class="regular-text">';
        echo '<p class="description">' . __('The Text you want to display on the banner.', 'upsoon') . '</p>';
    }

    public function render_banner_pos_field() {
        $positions = ['Bottom Right', 'Bottom Left', 'Top Right', 'Top Left', 'Top', 'Bottom', 'Left', 'Right'];
        $value = get_option('upsoon_banner_pos', 'Bottom Right');
        foreach ($positions as $pos) {
            echo '<label><input type="radio" name="upsoon_banner_pos" value="' . esc_attr($pos) . '"' . checked($value, $pos, false) . '> ' . esc_html($pos) . '</label><br>';
        }
        echo '<p class="description">' . __('The position of the banner on your site', 'upsoon') . '</p>';
    }

    public function render_banner_color_field() {
        $value = get_option('upsoon_banner_color', '#00008B');
        echo '<input type="color" name="upsoon_banner_color" value="' . esc_attr($value) . '">';
        echo '<p>' . __('Banner\'s background color', 'upsoon') . '</p>';
    }

    public function render_banner_text_color_field() {
        $value = get_option('upsoon_banner_text_color', '#FFFFFF');
        echo '<input type="color" name="upsoon_banner_text_color" value="' . esc_attr($value) . '">';
        echo '<p>' . __('Banner\'s text color', 'upsoon') . '</p>';
    }

    public function render_banner_border_radius_field() {
        $value = get_option('upsoon_banner_border_radius', '16px');
        echo '<input type="text" name="upsoon_banner_border_radius" value="' . esc_attr($value) . '" class="regular-text">';
        echo '<p>' . __('Default unit is px. Accepted units: px, em, %.', 'upsoon') . '</p>';
    }

    public function render_banner_font_size_field() {
        $value = get_option('upsoon_banner_font_size', '16px');
        echo '<input type="text" name="upsoon_banner_font_size" value="' . esc_attr($value) . '" class="regular-text">';
        echo '<p>' . __('Accepted units: px, em, rem, %, vw, vh.', 'upsoon') . '</p>';
    }

    public function render_banner_z_index_field() {
        $value = get_option('upsoon_banner_z_index', '9999');
        echo '<input type="text" name="upsoon_banner_z_index" value="' . esc_attr($value) . '" class="regular-text">';
        echo '<p>' . __('Z-Index, range: 1-9999.', 'upsoon') . '</p>';
    }

    public function render_banner_custom_css_field() {
        $default_css_path = UP_SOON_PLUGIN_DIR . 'public/assets/css/upsoon-banner.css';
        $default_css = file_exists($default_css_path) ? file_get_contents($default_css_path) : '';
        $custom_css_path = UP_SOON_PLUGIN_DIR . 'public/assets/css/upsoon-banner-custom.css';
        $custom_css = file_exists($custom_css_path) ? file_get_contents($custom_css_path) : '';

        echo '<textarea name="upsoon_banner_custom_css" rows="15" class="large-text code">' . esc_textarea($custom_css) . '</textarea>';
        wp_nonce_field('upsoon_save_css', 'upsoon_css_nonce');
        echo '<p>' . __('Customize the CSS for the banner. The original CSS is shown below for reference.', 'upsoon') . '</p>';
        echo '<details><summary>' . __('View Default CSS', 'upsoon') . '</summary><pre style="background:#f1f1f1;padding:1em;overflow:auto;max-height:400px;">' . esc_html($default_css) . '</pre></details>';
    }

    public function sanitize_banner_text($input) {
        return sanitize_text_field($input);
    }

    public function sanitize_banner_radius($input) {
        $input = sanitize_text_field($input);
        if (preg_match('/^\d+$/', $input)) {
            return $input . 'px';
        } elseif (preg_match('/^\d+(px|em|%)$/', $input)) {
            return $input;
        }
        return '16px';
    }

    public function sanitize_banner_font_size($input) {
        $input = sanitize_text_field($input);
        if (preg_match('/^\d+$/', $input)) {
            return $input . 'px';
        } elseif (preg_match('/^\d+(px|em|rem|%|vw|vh)$/', $input)) {
            return $input;
        }
        return '16px';
    }

    public function sanitize_banner_z_index($input) {
        $input = intval($input);
        return max(1, min($input, 9999));
    }

    public function maybe_save_custom_css() {
        if (!current_user_can('manage_options')) return;

        if (!isset($_POST['upsoon_css_nonce']) || !wp_verify_nonce($_POST['upsoon_css_nonce'], 'upsoon_save_css')) return;

        if (isset($_POST['upsoon_banner_custom_css'])) {
            $css = wp_strip_all_tags(stripslashes($_POST['upsoon_banner_custom_css']));

            list($is_valid, $error_message) = $this->validate_custom_css($css);
            if ($is_valid) {
                $file = UP_SOON_PLUGIN_DIR . 'public/assets/css/upsoon-banner-custom.css';
                try {
                    file_put_contents($file, $css);
                    add_settings_error(
                        'upsoon_settings',
                        'css_saved',
                        __('Custom CSS saved successfully.', 'upsoon'),
                        'updated'
                    );
                } catch (Exception $e) {
                    error_log('[UpSoon] Failed to write custom CSS: ' . $e->getMessage());
                    add_settings_error(
                        'upsoon_settings',
                        'css_write_failed',
                        __('Failed to write custom CSS to file.', 'upsoon'),
                        'error'
                    );
                }
            } else {
                add_settings_error(
                    'upsoon_settings',
                    'invalid_css',
                    __('Custom CSS contains syntax errors and was not saved.', 'upsoon'),
                    'error'
                );

                add_settings_error(
                    'upsoon_settings',
                    'invalid_css',
                    $error_message,
                    'error'
                );
            }
        }
    }


    private function validate_custom_css($css) {
        // Vérifie l'équilibre des accolades
        $open = substr_count($css, '{');
        $close = substr_count($css, '}');
        if ($open !== $close) {
            return [false, __('Unbalanced curly braces: check for missing { or }', 'upsoon')];
        }

        // Vérifie qu'il y a au moins une règle CSS
        if (!preg_match('/[a-zA-Z0-9\s\.\#\:\-\_\>\[\]=\*]+{[^}]+}/', $css)) {
            return [false, __('No valid CSS rules found (e.g. selector { property: value; })', 'upsoon')];
        }

        // Vérifie chaque ligne
        $lines = explode("\n", $css);
        foreach ($lines as $i => $line) {
            $line = trim($line);
            if (empty($line) || str_starts_with($line, '/*') || str_starts_with($line, '*')) continue;

            if (strpos($line, ':') !== false && !preg_match('/;[\s]*$/', $line)) {
                return [false, sprintf(__('Missing semicolon on line %d: "%s"', 'upsoon'), $i + 1, $line)];
            }

            if (strpos($line, ':') !== false && preg_match('/:[\s]*$/', $line)) {
                return [false, sprintf(__('Missing value after colon on line %d: "%s"', 'upsoon'), $i + 1, $line)];
            }
        }

        return [true, null];
    }

    public function render_settings_page() {
        if (!current_user_can('manage_options')) return;
        settings_errors('upsoon_settings');
        echo '<div class="wrap"><h1>' . esc_html(get_admin_page_title()) . '</h1>';
        echo '<form action="options.php" method="post">';
        settings_fields('upsoon_settings');
        do_settings_sections('upsoon_settings');
        submit_button();
        echo '</form></div>';
    }

    public function render_perso_page() {
        if (!current_user_can('manage_options')) return;
        settings_errors('upsoon_perso');
        echo '<div class="wrap"><h1>' . __('Customization', 'upsoon') . '</h1>';
        echo '<form action="options.php" method="post">';
        settings_fields('upsoon_perso');
        do_settings_sections('upsoon_perso');
        submit_button();
        echo '</form>';
        echo '<h2>' . __('Live Preview', 'upsoon') . '</h2>';
        echo '<div id="upsoon-preview-container" style="margin-top:20px;position:relative;height:200px;border:1px dashed #ccc;background:#f9f9f9;">
        <div id="upsoon-banner-preview" style="position:absolute;padding:10px;border-radius:16px;font-size:16px;z-index:9999;color:#FFFFFF;background-color:#00008B;">
            ' . esc_html(get_option('upsoon_banner_text', 'Test')) . '
        </div>
      </div>';
        echo '</div>';
    }

    public function render_history_page() {
        if (!current_user_can('manage_options')) return;
        echo '<div class="wrap"><h1>' . __('History', 'upsoon') . '</h1>';
        echo '<p>' . __('Work in progress', 'upsoon') . '</p></div>';
    }

    public function render_advanced_page() {
        if (!current_user_can('manage_options')) return;
        echo '<div class="wrap"><h1>' . __('Advanced Settings', 'upsoon') . '</h1>';
        echo '<form action="options.php" method="post">';
        settings_fields('upsoon_advanced');
        do_settings_sections('upsoon_advanced');
        submit_button();
        echo '</form></div>';
    }
}
