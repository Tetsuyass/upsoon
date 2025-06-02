<?php
/*
Plugin Name: UpSoon
Plugin URI: https://example.com
Description: a lightweight WordPress plugin that lets you display a customizable banner at the top or bottom of your site
Version: 1.0
Author: Tetsuya
Author URI: ""
License: GPLv3
Text Domain: upsoon
Domain Path: /languages
*/

//sécu
if(!defined('ABSPATH')) {
    exit;
}

//const
const UP_SOON_VERSION = '1.0';
define('UP_SOON_PLUGIN_DIR',plugin_dir_path(__FILE__));
define('UP_SOON_PLUGIN_URL',plugin_dir_url(__FILE__));
define('UP_SOON_PLUGIN_BASENAME',plugin_basename(__FILE__));

//autoloader pour require
/**
 *  Autoloader PSR-4 perso
 *  @param string $class Le nom complet de la classe courante.
 */

spl_autoload_register(function ($class) {
   //on comence par activer le débug
    $debug = true;
    //préfixe projet
    $prefix = 'UpSoon\\';
    $base_dir = UP_SOON_PLUGIN_DIR;

    if($debug) {
        error_log("=== AUTOLOADER DEBUG ===");
        error_log("Classe demandée: ".$class);
        error_log("Préfixe recherché: ".$prefix);
        error_log("Base dir: ".$base_dir);
    }

    //vérifier si la classe est à charger
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        if ($debug) error_log("La classe ne commence pas par le préfixe attendu, abandon.");
        return;
    }

    //nom relatif de la classe
    $relative_class = substr($class, $len);
    if($debug) error_log("Nom relatif de la classe : ".$relative_class);
    //déterminer le sous dossier avec le premier segment du namespace
    $parts = explode('\\', $relative_class);
    $first_segment = strtolower($parts[0]);

    if($debug) {
        error_log("Premier segment du namespace : ".$first_segment);
        error_log("Segments du namespace : ".print_r($parts, true));
    }

    //déterminer le sous dossier en fonction du premier segment du namespace
    if ($first_segment === 'admin') {
        $sub_dir = '/admin/';
    } else {
        $sub_dir = '/includes/';
    }

    if($debug) error_log("Sous dossier: ".$sub_dir);

    $class_name = end($parts);
    if($debug) {
        error_log("Nom de la classe : ".$class_name);
    }

    //construire le chemin du fichier avec le format upsoon-nomclasse.php
    $file = $base_dir . $sub_dir . 'upsoon-' . strtolower($class_name) . '.php';

    if ($debug) {
        error_log("Chemin du fichier à charger: ".$file);
        error_log("le fichier existe-t-il ? " . (file_exists($file)? "OUI" : "NON" ));
    }

    //si le fichier existe, on le charge
    if(file_exists($file)) {
        require $file;
        if($debug) {
            error_log("Fichier chargé avec succès ".$file);
            error_log("La classe existe-t-elle maintenant ? " . (class_exists($class)? "OUI" : "NON" ));
        }
    } else {
        error_log("ERREUR : Fichier introuvable. ".$file);

        //on vérifie quand même si le dossier existe
        $dir = dirname($file);
        if($debug) error_log("le dossier existe-t-il ?" . (is_dir($dir)? "OUI" : "NON" ));

        //on liste les fichiers du dossiers si il existe pour aider le débogage
        if (is_dir($dir)) {
            $files = scandir($dir);
            error_log("Contenu du dossier " . $dir . ":" . print_r($files, true));
        }
    }
    if($debug) error_log("=== FIN AUTOLOADER DEBUG ===");
});

/**
 * Init
 */

function upsoon_init() {
    $upsoon = new \UpSoon\Includes\Core();
    $upsoon->init();
}

//quand wp est chargé on initialise
add_action('plugins_loaded', 'upsoon_init');
//quand l'user active le plugin, on active
register_activation_hook(__FILE__,'upsoon_activate');

function upsoon_activate() {
    //déclaration des options utiles au plugin
    add_option('upsoon_banner_enabled', 'Deactivated'); //activer, désactiver la bannière
    add_option('upsoon_banner_text','Test'); //texte affiché sur la bannière
    add_option('upsoon_banner_pos','Bottom Right'); //position de la bannière sur le site
    add_option('upsoon_banner_color','#00008B'); //couleur de la bannière
    add_option('upsoon_banner_text_color','#FFFFFF'); //couleur du texte de la bannière
    //technique
    add_option('upsoon_banner_border_radius','16px'); //l'arrondissement des coins
    add_option('upsoon_banner_font_size','16px'); //taille du texte
    add_option('upsoon_banner_z_index','9999'); //s'affiche au dessus des autres objets
    add_option('upsoon_banner_custom_css', ''); //pour les dev qui veulent ajouter du css en plus
}

//pas besoin de hook de désinsta on utilise uninstall.php
