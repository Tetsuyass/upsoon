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
delete_option('upsoon_banner_enabled');
delete_option('upsoon_banner_text');
delete_option('upsoon_banner_pos');
delete_option('upsoon_banner_color');
delete_option('upsoon_banner_text_color');
delete_option('upsoon_banner_border_radius');
delete_option('upsoon_banner_font_size');
delete_option('upsoon_banner_z_index');
delete_option('upsoon_banner_custom_css');
