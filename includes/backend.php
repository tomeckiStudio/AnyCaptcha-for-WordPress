<?php

add_action('admin_menu', 'tsac_options_page');
function tsac_options_page() {
	add_menu_page(
		__('AnyCaptcha', 'ts-any-captcha'),
		__('AnyCaptcha', 'ts-any-captcha'),
		'manage_options',
		'tsac',
		'tsac_options_page_html',
		'dashicons-filter'
	);
}

add_action('admin_init', 'tsac_settings_init');
function tsac_settings_init() {
	register_setting('tsac', 'tsac_options');
	
	// Google reCaptcha Settings
	add_settings_section(
		'tsac_section_grecaptcha_options',
		__('Google reCaptcha Settings', 'ts-any-captcha'), 
		'tsac_section_anycaptcha_callback',
		'tsac'
	);

	add_settings_field(
		'tsac_grecaptcha_enable',
		__('Turn on Google reCaptcha', 'ts-any-captcha'),
		'tsac_captcha_enable_callback',
		'tsac',
		'tsac_section_grecaptcha_options',
		array(
			'tsac_grecaptcha_enable' 
		) 
	);
	
	add_settings_field(
		'tsac_grecaptcha_site_key',
		__('Site Key', 'ts-any-captcha'),
		'tsac_captcha_text_callback',
		'tsac',
		'tsac_section_grecaptcha_options',
		array(
			'tsac_grecaptcha_site_key' 
		) 
	);
	add_settings_field(
		'tsac_grecaptcha_secret_key',
		__('Secret Key', 'ts-any-captcha'),
		'tsac_captcha_text_callback',
		'tsac',
		'tsac_section_grecaptcha_options',
		array(
			'tsac_grecaptcha_secret_key' 
		) 
	);
	
	
	// mathCaptcha Settings
	add_settings_section(
		'tsac_section_mathcaptcha_options',
		__('mathCaptcha Settings', 'ts-any-captcha'), 
		'tsac_section_anycaptcha_callback',
		'tsac'
	);

	add_settings_field(
		'tsac_mathcaptcha_enable',
		__('Turn on mathCaptcha', 'ts-any-captcha'),
		'tsac_captcha_enable_callback',
		'tsac',
		'tsac_section_mathcaptcha_options',
		array(
			'tsac_mathcaptcha_enable' 
		) 
	);
	
	
	// imageCaptcha Settings
	add_settings_section(
		'tsac_section_imagecaptcha_options',
		__('imageCaptcha Settings', 'ts-any-captcha'), 
		'tsac_section_anycaptcha_callback',
		'tsac'
	);

	add_settings_field(
		'tsac_imagecaptcha_enable',
		__('Turn on imageCaptcha', 'ts-any-captcha'),
		'tsac_captcha_enable_callback',
		'tsac',
		'tsac_section_imagecaptcha_options',
		array(
			'tsac_imagecaptcha_enable' 
		) 
	);
	
}


function tsac_section_anycaptcha_callback($args) {
	
}

function tsac_captcha_enable_callback($args) {
	$options = get_option('tsac_options');
	$checked = "";
	
	if(isset($options[$args[0]])){
		$checked = "checked";
	}
	
	?>
	<input type="checkbox" id="<?php echo $args[0]; ?>" name="tsac_options[<?php echo $args[0]; ?>]" value="true" <?php echo $checked; ?> />
	<?php
}

function tsac_captcha_text_callback($args) {
	$options = get_option('tsac_options');
	$value = "";
	
	if(isset($options[$args[0]])){
		$value = $options[$args[0]];
	}
	
	?>
	<input type="text" id="<?php echo $args[0]; ?>" name="tsac_options[<?php echo $args[0]; ?>]" value="<?php echo $value; ?>" />
	<?php
}


function tsac_options_page_html() {
	if(!current_user_can('manage_options')){
		return;
	}

	if(isset($_GET['settings-updated'])){
		add_settings_error('tsac_messages', 'tsac_message', __('Settings Saved', 'ts-any-captcha'), 'updated');
	}

	settings_errors('tsac_messages');
	
	?>
	<div class="wrap">
		<h1><?php echo esc_html(get_admin_page_title()); ?></h1>
		<form action="options.php" method="post">
			<?php
			settings_fields('tsac');
			do_settings_sections('tsac');
			submit_button('Save Settings');
			?>
		</form>
	</div>
	<?php
}
