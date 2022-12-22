<?php
/*
 * Plugin Name:       AnyCaptcha
 * Description:       Google reCaptcha and mathCaptcha for WordPress. It also has integration with Elementor forms, Contact Form 7 and WooCommerce.
 * Version:           1.0.1
 * Author:            tomeckiStudio
 * Author URI:        https://tomecki.studio/
 * Text Domain:       ts-any-captcha
 * License:           GPL-3.0+
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('ABSPATH') or die('You do not have permissions to this file!');

define('TS_ANY_CAPTCHA_PATH', __DIR__);
define('TS_ANY_CAPTCHA_DIR', plugin_dir_url(__FILE__));

add_action('init', 'tsac_init');
function tsac_init(){
	if(session_status() !== PHP_SESSION_ACTIVE && !headers_sent())
		session_start();
	
	if(is_admin() && current_user_can('administrator')){
		include_once 'includes/backend.php';
		
		add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'tsac_plugin_action_links');
	}
	
	$options = get_option('tsac_options');
	
	if(isset($options['tsac_mathcaptcha_enable'])){
		if($options['tsac_mathcaptcha_enable'] == "true"){
			include_once 'includes/math_captcha.php';
			
			if($_SERVER['REQUEST_METHOD'] !== 'POST'){
				$tsac_math_captcha = MathCaptcha::get_instance();
				$tsac_math_captcha->new_captcha();
			}
		}
	}
	
	if(isset($options['tsac_imagecaptcha_enable'])){
		if($options['tsac_imagecaptcha_enable'] == "true"){
			include_once 'includes/image_captcha.php';
			
			if($_SERVER['REQUEST_METHOD'] !== 'POST'){
				$tsac_image_captcha = ImageCaptcha::get_instance();
				$tsac_image_captcha->new_captcha();
			}
		}
	}
	
	include_once 'includes/frontend.php';
}

if (in_array('elementor-pro/elementor-pro.php', get_option('active_plugins'))){
	function tsac_elementor_add_fields($form_fields_registrar) {
		$options = get_option('tsac_options');
		
		if(isset($options['tsac_mathcaptcha_enable'])){
			if($options['tsac_mathcaptcha_enable'] == "true"){
				require_once(TS_ANY_CAPTCHA_PATH . '/integrations/elementor/math_captcha_field.php');
				$form_fields_registrar->register(new \Elementor_Math_Captcha_Field());
			}
		}
		
		if(isset($options['tsac_imagecaptcha_enable'])){
			if($options['tsac_imagecaptcha_enable'] == "true"){
				require_once(TS_ANY_CAPTCHA_PATH . '/integrations/elementor/image_captcha_field.php');
				$form_fields_registrar->register(new \Elementor_Image_Captcha_Field());
			}
		}
	}
	add_action('elementor_pro/forms/fields/register', 'tsac_elementor_add_fields');
}
function tsac_plugin_action_links($links) {
	return array_merge(
		array(
			'settings' => '<a href="/wp-admin/admin.php?page=tsac">' . __( 'Settings', 'ts-any-captcha' ) . '</a>',
			'about-author' => '<a href="https://tomecki.studio/">' . __( 'About Author', 'ts-any-captcha' ) . '</a>'
		),
		$links
	);
}

function tsac_load_textdomain() {
    load_plugin_textdomain('ts-any-captcha', false, dirname(plugin_basename(__FILE__)) . '/languages');
}
add_action('plugins_loaded', 'tsac_load_textdomain');

function tsac_activate() { 
	update_option('tsac_hash_key', generateRandomString());
	
	register_uninstall_hook(__FILE__, 'tsac_uninstall');
}
register_activation_hook(__FILE__, 'tsac_activate');

function generateRandomString(){
	$digits = "1234567890";
	$specials = "#$%&@^`~.,:;/|_-<*+!?={}[]()";
	$letters = "abcdefghijklmnoupqrstuvwxyz";
	$letters_uppercase = "ABCDEFGHIJKLMNOUPQRSTUVWXYZ";
	$chars = $letters . $specials . $letters_uppercase . $digits;

	$length_of_chars = strlen($chars);
	
	for ($i = $length_of_chars - 1; $i > 0; $i--) {
		$j = rand(1, $length_of_chars);
		$temp = $chars[$i];
		$chars[$i] = $chars[$j];
		$chars[$j] = $temp;
	}


	$randomString = "";
	for ($x = 0; $x < 6; $x++) {
		$randomString .= $chars[rand(1, $length_of_chars)];
	}

	return $randomString;
}
function tsac_uninstall(){
	delete_option('tsac_hash_key');
	delete_option('tsac_options');
}