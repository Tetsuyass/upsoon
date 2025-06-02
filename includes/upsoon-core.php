<?php
/**
 * Coeur du plugin, ne pas toucher.
 *
 * @package UpSoon
 */
namespace UpSoon\Includes;

if(!defined('ABSPATH')) {
    exit;
}

class Core {
    /**
     * Init
     */
    public function init() {
        add_action('init',array($this,'load_textdomain')); //traduction
        $this->init_components(); //initialisation des composants
        add_action('wp_enqueue_scripts',array($this,'load_scripts')); //on met les scripts en attente
    }

    /**
     * Charger le domaine de texte (la langue)
     * @return void
     */
    public function load_textdomain() {
        load_plugin_textdomain(
          'upsoon', //domaine de texte
          false, //chemin relatif au rép wordpress (obsolète)
          dirname(UP_SOON_PLUGIN_BASENAME).'/languages/' //chemin vers traductions
        );
    }

    /**
     * Init les composants
     * @return void
     */
    public function init_components() {
        //si l'user est admin on init l'admin
        if(is_admin()) {
            $admin = new \UpSoon\Admin\Admin();
            $admin->init();
            //init la bannière :
            $pub = new Pub();
            $pub->init();
        }
    }

    /**
     * Charger les scripts
     * @return void
     */
    public function load_scripts() {
        wp_register_style(
            'upsoon-public', //id du style
            UP_SOON_PLUGIN_URL.'css/upsoon-public.css', //chemin vers la stylesheet
            array(), //dépendances
            UP_SOON_VERSION
        );
        wp_enqueue_style ( 'upsoon-public' );
        wp_register_script(
            'upsoon-public',
            UP_SOON_PLUGIN_URL . 'js/upsoon-public-js',
            array(),
            UP_SOON_VERSION,
            true //init dans le header
        );
    }
}