<?php

namespace UpSoon\Admin;

//sécu
if(!defined('ABSPATH')) {
    exit;
}

class Admin
{

    /**
     * Init de l'admin
     */

    public function init() {
        //ajout du menu admin à l'initialisation
        add_action('admin_menu', array($this,'add_admin_menu'));
        //enregistrement des settings utilisateurs
        add_action('admin_init', array($this,'register_settings'));
        //enfilage des scripts et templates admin
        add_action('admin_enqueue_scripts', array($this,'enqueue_admin_scripts'));
        //success ou pas après enregistrement des param
        add_action('admin_init', array($this,'settings_saved_notice'));
        //entrypoint ajax
        $this->register_ajax_handlers();
    }

    /**
     * Affiche un message de succès après l'enregistrement des paramètres
     * @return void
     */
    public function settings_saved_notice() {
        if(isset($_GET['settings-updated']) && $_GET['settings-updated'] == 'true' &&
           isset($_GET['page']) && strpos($_GET['page'], 'upsoon-') === 0) {
            //messsage
            add_settings_error(
                'upsoon_settings',
                'settings_updated',
                __('Settings saved successfully !','upsoon'),
                'updated'
            );
        }
    }

    public function add_admin_menu() {
        add_menu_page(
            __('UpSoon Settings','upsoon'),
            __('UpSoon','upsoon'),
            'manage_options',
            'upsoon-settings',
            array($this,'render_settings_page'),
            'dashicon-banner',
            120
        );

        add_submenu_page(
            'upsoon-settings',
            __('UpSoon Settings','upsoon'),
            __('Settings','upsoon'),
            'manage_options',
            'upsoon-settings',
            array($this,'render_settings_page')
        );

        add_submenu_page(
            'upsoon-settings',
            __('UpSoon Customization','upsoon'),
            __('Customization','upsoon'),
            'manage_options',
            'upsoon-perso',
            array($this,'render_perso_page')
        );

        add_submenu_page(
            'upsoon-settings',
            __('UpSoon Display Rules','upsoon'),
            __('Display','upsoon'),
            'manage_options',
            'upsoon-conditions',
            array($this,'render_conditions_page')
        );

        add_submenu_page(
            'upsoon-settings',
            __('UpSoon History','upsoon'),
            __('History','upsoon'),
            'manage_options',
            'upsoon-history',
            array($this,'render_history_page')
        );

        add_submenu_page(
            'upsoon-settings',
            __('UpSoon Advanced Settings','upsoon'),
            __('Advanced','upsoon'),
            'manage_options',
            'upsoon-advanced',
            array($this,'render_advanced_page')
        );
    }

    public function register_settings() {
        //register options utilisateurs + fonctions de sanitization
        register_setting('upsoon-settings', 'upsoon_banner_enabled');
        register_setting('upsoon_settings', 'upsoon_banner_text', array('sanitize_callback' => array($this, 'sanitize_banner_text')));
        register_setting('upsoon_settings', 'upsoon_banner_pos');
        register_setting('upsoon_settings', 'upsoon_banner_color');
        register_setting('upsoon_settings', 'upsoon_banner_text_color');
        //advanced
        register_setting('upsoon_settings', 'upsoon_banner_border_radius', array('sanitize_callback' => array($this, 'sanitize_banner_radius')));
        register_setting('upsoon_settings', 'upsoon_banner_font_size', array('sanitize_callback' => array($this, 'sanitize_banner_font_size')));
        register_setting('upsoon_settings', 'upsoon_banner_z_index');
        register_setting('upsoon_settings', 'upsoon_banner_custom_css'); //ici je ferais un système qui affichera le css de base de la bannière pour donner plus de libertés au développeur. Je pourrais ensuite faire une copie de ce nouveau fichier css pour l'enregistrer dans assets et changer le chemin d'accès pour inclure ce fichier !

        //Sections :
        add_settings_section(
            'upsoon_section_base',
            __('Base Settings'),
            array($this,'render_section_base'),
            'upsoon_settings'
        );

        add_settings_section(
            'upsoon_section_advanced',
            __('Advanced Settings','upsoon'),
            array($this,'render_section_advanced'),
            'upsoon_settings'
        );

        //Champs
        add_settings_field(
            'upsoon_banner_enabled',
            __('Enable Banner','upsoon'),
            array($this,'render_banner_enbaled_field'),
            'upsoon_settings',
            'upsoon_section_base'
        );

        add_settings_field(
            'upsoon_banner_text',
            __('Banner Text','upsoon'),
            array($this,'render_banner_text_field'),
            'upsoon_settings',
            'upsoon_section_base'
        );

        add_settings_field(
            'upsoon_banner_pos',
            __('Banner Position','upsoon'),
            array($this,'render_banner_pos_field'),
            'upsoon_settings',
            'upsoon_section_base'
        );

        add_settings_field(
            'upsoon_banner_color',
            __('Banner Color', 'upsoon'),
            array($this, 'render_banner_color_field'),
            'upsoon_settings',
            'upsoon_section_base'
        );

        add_settings_field(
            'upsoon_banner_text_color',
            __('Banner Text Color', 'upsoon'),
            array($this, 'render_banner_text_color_field'),
            'upsoon_settings',
            'upsoon_section_base'
        );

        add_settings_field(
            'upsoon_banner_border_radius',
            __('Banner Border Radius','upsoon'),
            array($this, 'render_banner_border_radius_field'),
            'upsoon_settings',
            'upsoon_section_advanced'
        );

        add_settings_field(
            'upsoon_banner_font_size',
            __('Banner Font Size','upsoon'),
            array($this, 'render_banner_font_size_field'),
            'upsoon_settings',
            'upsoon_section_advanced'
        );

        add_settings_field(
            'upsoon_banner_z_index',
            __('Banner Z Index', 'upsoon'),
            array($this, 'render_banner_z_index_field'),
            'upsoon_settings',
            'upsoon_section_advanced'
        );

        add_settings_field(
            'upsoon_banner_custom_css',
            __('Banner Custom CSS','upsoon'),
            array($this, 'render_banner_custom_css_field'),
            'upsoon_settings',
            'upsoon_section_advanced'
        );
    }

    /**
     * Enfilage des scripts admin
     * @return void
     */
    //eniflage des scripts admin

    public function enqueue_admin_scripts() {
        //recherche de la chaîne 'upsoon' dans l'id de la page
        if(strpos($hook,'upsoon-') !== false){
            wp_enqueue_style(
                'upsoon-admin',
                UP_SOON_PLUGIN_URL . 'admin/css/upsoon-admin.css',
                array(),
                UP_SOON_VERSION
            );

            wp_enqueue_script(
                'upsoon-admin',
                UP_SOON_PLUGIN_URL . 'admin/js/upsoon-admin.js',
                array(),
                UP_SOON_VERSION,
                true
            );
        }
    }

    // Callbacks pour l'affichage des fonctions

    /**
     * Display de la section base des settings
     * @return void
     */
    public function render_section_base() {
        echo '<p>'.__('Base display settings for the banner').'</p>';
    }

    /**
     * Display de la section advanced des settings
     * @return void
     */
    public function render_section_advanced() {
        echo '<p>'.__('Advanced display settings for the banner').'</p>';
    }

    /**
     * Display du champ pour l'activation/désactiavtion de la bannière
     * @return void
     */
    public function render_banner_enbaled_field() {
        $value = isset($_POST['upsoon_banner_enabled']) ? $_POST['upsoon_banner_enabled'] : 'Deactivated';
        echo '<input type="radio" name="upsoon_banner_enabled" value="'.esc_attr('Activated').'"'.checked($value, 'Activated',false).'>';
        echo '<input type="radio" name="upsoon_banner_enabled" value="'.esc_attr('Deactivated').'"'.checked($value, 'Deactivated',false).'>';
        echo '<p class="description">' . __('Enable or Disable Banner.', 'upsoon') . '</p>';
    }

    /**
     * Display du champ pour le texte affiché sur la bannière
     * @return void
     */
    public function render_banner_text_field() {
        $value = isset($_POST['upsoon_banner_text']) ? sanitize_text_field($_POST['upsoon_banner_text']) : 'Test';
        echo '<input type="text" name="upsoon_banner_text" value="'.esc_attr($value).'" class="regular-text">';
        echo '<p class="description">'.__('The Text you want to display on the banner.', 'upsoon').'</p>';
    }

    /**
     * Display du champ pour la position de la bannière
     * @return void
     */
    public function render_banner_pos_field() {
        $value = isset($_POST['upsoon_banner_pos']) ? $_POST['upsoon_banner_pos'] : 'Bottom Right';
        echo '<input type="radio" name="upsoon_banner_pos" value="'.esc_attr('Bottom Right').'"'.checked($value, 'Bottom Right',false).'>';
        echo '<input type="radio" name="upsoon_banner_pos" value="'.esc_attr('Bottom Left').'"'.checked($value, 'Bottom Left',false).'>';
        echo '<input type="radio" name="upsoon_banner_pos" value="'.esc_attr('Top Right').'"'.checked($value, 'Top Right',false).'>';
        echo '<input type="radio" name="upsoon_banner_pos" value="'.esc_attr('Top Left').'"'.checked($value, 'Top Left',false).'>';
        echo '<input type="radio" name="upsoon_banner_pos" value="'.esc_attr('Top').'"'.checked($value, 'Top',false).'>';
        echo '<input type="radio" name="upsoon_banner_pos" value="'.esc_attr('Bottom').'"'.checked($value, 'Bottom',false).'>';
        echo '<input type="radio" name="upsoon_banner_pos" value="'.esc_attr('Left').'"'.checked($value, 'Left',false).'>';
        echo '<input type="radio" name="upsoon_banner_pos" value="'.esc_attr('Right').'"'.checked($value, 'Right',false).'>';
        '<p class="description">'.__('The position of the banner on your site','upsoon').'</p>';
    }

    /**
     * Display du champ pour la couleur de la bannière
     * @return void
     */
    public function render_banner_color_field() {
        $value = isset($_POST['upsoon_banner_color']) ? $_POST['upsoon_banner_color'] : '#00008B';
        echo '<input type="color" name="upsoon_banner_color" value=">';
    }
}