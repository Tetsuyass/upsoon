<?php
/**
 * S'active quand l'user supprime le plugin de WordPress
 *
 * @package UpSoon
 */
//sécu
if(!defined('ABSPATH')) {
    exit;
}

//supprimer toutes les options
delete_option('upsoon_banner_enabled', true);
delete_option('upsoon_banner_text','Test');
delete_option('upsoon_banner_pos','bottom-right');
delete_option('upsoon_banner_color','blue');
delete_option('upsoon_banner_text_color','white');
delete_option('upsoon_banner_border_radius','16px');
delete_option('upsoon_banner_font_size','16px');
delete_option('upsoon_banner_z_index','9999');
delete_option('upsoon_banner_custom_css','');
