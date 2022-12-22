<?php

add_action('login_enqueue_scripts', 'tsscp_styles');
add_action('wp_enqueue_scripts', 'tsscp_styles');


function tsscp_styles() {
	$options = get_option('tsac_options');
	
	if(isset($options['tsac_imagecaptcha_enable'])){
		if($options['tsac_imagecaptcha_enable'] == "true"){
			wp_register_style('tsac_frontend_styles', TS_ANY_CAPTCHA_DIR . '/assets/css/tsac_styles.css');
			wp_enqueue_style('tsac_frontend_styles');
		}
	}
}

add_action('lostpassword_form',  'ts_any_captcha_display');
add_action('login_form',  'ts_any_captcha_display');
add_action('register_form',  'ts_any_captcha_display');
function ts_any_captcha_display(){
	$options = get_option('tsac_options');
	
	if(isset($options['tsac_grecaptcha_enable'])){
		if($options['tsac_grecaptcha_enable'] == "true"){
			?>
			<script>
				var onloadCallback = function() {
					recaptcha = grecaptcha.render(document.getElementById('recaptcha'), {
						'sitekey' : '<?php echo $options['tsac_grecaptcha_site_key']; ?>'
					});
				};
			</script>
			<div id="recaptcha" class="recaptcha"></div>
			<script src="https://www.google.com/recaptcha/api.js?onload=onloadCallback"></script>

			<?php
		}
	}

	if(isset($options['tsac_mathcaptcha_enable'])){
		if($options['tsac_mathcaptcha_enable'] == "true"){
			$tsac_math_captcha = MathCaptcha::get_instance();
			?>
			
			<div>
				<p>
					<?php echo __("Solve the equation: ", "ts-any-captcha") . $tsac_math_captcha->get_captcha_text() . " = "; ?>
				</p>
				<input type="number" name="tsac_math_captcha" />
			</div>

			<?php
		}
	}
	
	if(isset($options['tsac_imagecaptcha_enable'])){
		if($options['tsac_imagecaptcha_enable'] == "true"){
			$tsac_image_captcha = ImageCaptcha::get_instance();
			?>

			<div class="image-captcha">
				<p class="ic-title">
					<?php echo sprintf(__("Prove you're human by choosing the %s icon: ", "ts-any-captcha"), "<span class='selected-icon-name'>" . $tsac_image_captcha->get_captcha_result_name() . "</span>"); ?>
				</p>
				<?php echo $tsac_image_captcha->get_captcha_selected_icons(); ?>
				<input type="hidden" name="tsac_image_captcha_result" value="<?php echo hash_hmac('md5', $tsac_image_captcha->get_captcha_result(), get_option('tsac_hash_key')); ?>">
</div>

			<?php
		}
	}
}

add_action('lostpassword_post' , 'tsac_wp_lostpassword_post', 10, 2);
function tsac_wp_lostpassword_post($errors, $user_data){
	$options = get_option('tsac_options');
		
	if(isset($options['tsac_grecaptcha_enable'])){
		if($options['tsac_grecaptcha_enable'] == "true"){
			$response = wp_remote_post('https://www.google.com/recaptcha/api/siteverify', [
				'body' => [
					'secret'   => $options['tsac_grecaptcha_secret_key'],
					'response' => $_POST['g-recaptcha-response'],
					'remoteip' => $_SERVER['REMOTE_ADDR']
				]
			]);

			$response_code = wp_remote_retrieve_response_code($response);
			$response_body = wp_remote_retrieve_body($response);

			if($response_code != 200)
				$errors->add('invalid_captcha', __('<strong>ERROR</strong>: Solve Google reCaptcha correctly.', 'ts-any-captcha'));
			
			$result = json_decode($response_body, true);

			if(!$result['success'])
				$errors->add('invalid_captcha', __('<strong>ERROR</strong>: Solve Google reCaptcha correctly.', 'ts-any-captcha'));	
			
		}
	}
	
	if(isset($options['tsac_mathcaptcha_enable'])){
		if($options['tsac_mathcaptcha_enable'] == "true"){
			if(empty($_POST['tsac_math_captcha']))
				$errors->add('invalid_captcha', __('<strong>ERROR</strong>: Solve mathCaptcha correctly.', 'ts-any-captcha'));
			
			$tsac_math_captcha = MathCaptcha::get_instance();
			
			if(!$tsac_math_captcha->verify($_POST['tsac_math_captcha']))
				$errors->add('invalid_captcha', __('<strong>ERROR</strong>: Solve mathCaptcha correctly.', 'ts-any-captcha'));
			
			$tsac_math_captcha->new_captcha();
		}
	}
	
	if(isset($options['tsac_imagecaptcha_enable'])){
		if($options['tsac_imagecaptcha_enable'] == "true"){
			if(empty($_POST['tsac_image_captcha']))
				$errors->add('invalid_captcha', __('<strong>ERROR</strong>: Solve imageCaptcha correctly.', 'ts-any-captcha'));
			
			$tsac_image_captcha = ImageCaptcha::get_instance();
			
			if(!$tsac_image_captcha->verify($_POST['tsac_image_captcha']))
				$errors->add('invalid_captcha', __('<strong>ERROR</strong>: Solve imageCaptcha correctly.', 'ts-any-captcha'));
			
			$tsac_image_captcha->new_captcha();
		}
	}
}

add_filter('wp_authenticate_user', 'tsac_wp_authenticate_user', 10, 2);
function tsac_wp_authenticate_user($user, $password) {
	$options = get_option('tsac_options');
		
	if(isset($options['tsac_grecaptcha_enable'])){
		if($options['tsac_grecaptcha_enable'] == "true"){
			$response = wp_remote_post('https://www.google.com/recaptcha/api/siteverify', [
				'body' => [
					'secret'   => $options['tsac_grecaptcha_secret_key'],
					'response' => $_POST['g-recaptcha-response'],
					'remoteip' => $_SERVER['REMOTE_ADDR']
				]
			]);

			$response_code = wp_remote_retrieve_response_code($response);
			$response_body = wp_remote_retrieve_body($response);

			if($response_code != 200)
				return new WP_Error('invalid_captcha', __('Solve Google reCaptcha correctly.', 'ts-any-captcha'));
			
			$result = json_decode($response_body, true);

			if(!$result['success'])
				return new WP_Error('invalid_captcha', __('Solve Google reCaptcha correctly.', 'ts-any-captcha'));	
			
		}
	}
	
	if(isset($options['tsac_mathcaptcha_enable'])){
		if($options['tsac_mathcaptcha_enable'] == "true"){
			$tsac_math_captcha = MathCaptcha::get_instance();
			
			if(empty($_POST['tsac_math_captcha'])){
				$tsac_math_captcha->new_captcha();
				return new WP_Error('invalid_captcha', __('Solve mathCaptcha correctly.', 'ts-any-captcha'));
			}
			
			if(!$tsac_math_captcha->verify($_POST['tsac_math_captcha'])){
				$tsac_math_captcha->new_captcha();
				return new WP_Error('invalid_captcha', __('Solve mathCaptcha correctly.', 'ts-any-captcha'));
			}
		}
	}
	
	if(isset($options['tsac_imagecaptcha_enable'])){
		if($options['tsac_imagecaptcha_enable'] == "true"){
			$tsac_image_captcha = ImageCaptcha::get_instance();
			
			if(empty($_POST['tsac_image_captcha'])){
				$tsac_image_captcha->new_captcha();
				return new WP_Error('invalid_captcha', __('Solve imageCaptcha correctly.', 'ts-any-captcha'));
			}
			
			if(!$tsac_image_captcha->verify($_POST['tsac_image_captcha'])){
				$tsac_image_captcha->new_captcha();
				return new WP_Error('invalid_captcha', __('Solve imageCaptcha correctly.', 'ts-any-captcha'));
			}
		}
	}
	
	return $user;
}

add_filter('registration_errors', 'tsac_wp_registration_errors', 10, 3);
function tsac_wp_registration_errors($errors, $sanitized_user_login, $user_email) {
	$options = get_option('tsac_options');
		
	if(isset($options['tsac_grecaptcha_enable'])){
		if($options['tsac_grecaptcha_enable'] == "true"){
			$response = wp_remote_post('https://www.google.com/recaptcha/api/siteverify', [
				'body' => [
					'secret'   => $options['tsac_grecaptcha_secret_key'],
					'response' => $_POST['g-recaptcha-response'],
					'remoteip' => $_SERVER['REMOTE_ADDR']
				]
			]);

			$response_code = wp_remote_retrieve_response_code($response);
			$response_body = wp_remote_retrieve_body($response);

			if($response_code != 200)
				$errors->add('invalid_captcha', __('Solve Google reCaptcha correctly.', 'ts-any-captcha'));
			
			$result = json_decode($response_body, true);

			if(!$result['success'])
				$errors->add('invalid_captcha', __('Solve Google reCaptcha correctly.', 'ts-any-captcha'));	
			
		}
	}
	
	if(isset($options['tsac_mathcaptcha_enable'])){
		if($options['tsac_mathcaptcha_enable'] == "true"){
			$tsac_math_captcha = MathCaptcha::get_instance();
			
			if(empty($_POST['tsac_math_captcha'])){
				$tsac_math_captcha->new_captcha();
				$errors->add('invalid_captcha', __('Solve mathCaptcha correctly.', 'ts-any-captcha'));
			}
			
			if(!$tsac_math_captcha->verify($_POST['tsac_math_captcha'])){
				$tsac_math_captcha->new_captcha();
				$errors->add('invalid_captcha', __('Solve mathCaptcha correctly.', 'ts-any-captcha'));
			}
		}
	}
	
	if(isset($options['tsac_imagecaptcha_enable'])){
		if($options['tsac_imagecaptcha_enable'] == "true"){
			$tsac_image_captcha = ImageCaptcha::get_instance();
			
			if(empty($_POST['tsac_image_captcha'])){
				$tsac_image_captcha->new_captcha();
				$errors->add('invalid_captcha', __('Solve imageCaptcha correctly.', 'ts-any-captcha'));
			}
			
			if(!$tsac_image_captcha->verify($_POST['tsac_image_captcha'])){
				$tsac_image_captcha->new_captcha();
				$errors->add('invalid_captcha', __('Solve imageCaptcha correctly.', 'ts-any-captcha'));
			}
		}
	}
	
	return $errors;
}

function tsac_comment_form_defaults($submit_field) {
	$options = get_option('tsac_options');
	
	if(isset($options['tsac_grecaptcha_enable'])){
		if($options['tsac_grecaptcha_enable'] == "true"){
			$recaptcha_site_key = $options['tsac_grecaptcha_site_key'];
			
			$submit_field['submit_field'] = '<div class="g-recaptcha" data-sitekey="' . $recaptcha_site_key . '"></div>
				<script src="https://www.google.com/recaptcha/api.js?onload=onloadCallback"></script><br>' . $submit_field["submit_field"];
		}
	}
	
	if(isset($options['tsac_mathcaptcha_enable'])){
		if($options['tsac_mathcaptcha_enable'] == "true"){
			$tsac_math_captcha = MathCaptcha::get_instance();
			
			$submit_field['submit_field'] = '<div>
				<p>
					' . __("Solve the equation: ", "ts-any-captcha") . $tsac_math_captcha->get_captcha_text() . ' = 
				</p>
				<input type="number" name="tsac_math_captcha" />
			</div>' . $submit_field["submit_field"];
		}
	}
	
	if(isset($options['tsac_imagecaptcha_enable'])){
		if($options['tsac_imagecaptcha_enable'] == "true"){
			$tsac_image_captcha = ImageCaptcha::get_instance();
			
			$text = __("Prove you're human by choosing the %s icon: ", "ts-any-captcha");	
			
			$submit_field['submit_field'] = '<div class="image-captcha">
				<p class="ic-title">
					' . sprintf($text, "<span class=\"selected-icon-name\">" . $tsac_image_captcha->get_captcha_result_name() . "</span>") . '
				</p>
				' . $tsac_image_captcha->get_captcha_selected_icons() . '
			</div>' . $submit_field["submit_field"];
		}
	}
	
	return $submit_field;
}
add_filter('comment_form_defaults','tsac_comment_form_defaults');

function tsac_pre_comment_on_post($comment_post_id) {
	$options = get_option('tsac_options');
		
	if(isset($options['tsac_grecaptcha_enable'])){
		if($options['tsac_grecaptcha_enable'] == "true"){
			$response = wp_remote_post('https://www.google.com/recaptcha/api/siteverify', [
				'body' => [
					'secret'   => $options['tsac_grecaptcha_secret_key'],
					'response' => $_POST['g-recaptcha-response'],
					'remoteip' => $_SERVER['REMOTE_ADDR']
				]
			]);

			$response_code = wp_remote_retrieve_response_code($response);
			$response_body = wp_remote_retrieve_body($response);

			if($response_code != 200)
				wp_die(
					'<p>' . __('Solve Google reCaptcha correctly.', 'ts-any-captcha') . '</p>',
					__('Comment Submission Failure.', 'ts-any-captcha'),
					array(
						'response'  => $data,
						'back_link' => true,
					)
				);
			
			$result = json_decode($response_body, true);

			if(!$result['success'])
				wp_die(
					'<p>' . __('Solve Google reCaptcha correctly.', 'ts-any-captcha') . '</p>',
					__('Comment Submission Failure.', 'ts-any-captcha'),
					array(
						'response'  => $data,
						'back_link' => true,
					)
				);
			
		}
	}
	
	if(isset($options['tsac_mathcaptcha_enable'])){
		if($options['tsac_mathcaptcha_enable'] == "true"){
			$tsac_math_captcha = MathCaptcha::get_instance();
			
			if(empty($_POST['tsac_math_captcha'])){
				$tsac_math_captcha->new_captcha();
				wp_die(
					'<p>' . __('Solve mathCaptcha correctly.', 'ts-any-captcha') . '</p>',
					__('Comment Submission Failure.', 'ts-any-captcha'),
					array(
						'response'  => $data,
						'back_link' => true,
					)
				);
			}
			
			if(!$tsac_math_captcha->verify($_POST['tsac_math_captcha'])){
				$tsac_math_captcha->new_captcha();
				wp_die(
					'<p>' . __('Solve mathCaptcha correctly.', 'ts-any-captcha') . '</p>',
					__('Comment Submission Failure.', 'ts-any-captcha'),
					array(
						'response'  => $data,
						'back_link' => true,
					)
				);
			}
		}
	}
	
	if(isset($options['tsac_imagecaptcha_enable'])){
		if($options['tsac_imagecaptcha_enable'] == "true"){
			$tsac_image_captcha = ImageCaptcha::get_instance();
			
			if(empty($_POST['tsac_image_captcha'])){
				$tsac_image_captcha->new_captcha();
				wp_die(
					'<p>' . __('Solve imageCaptcha correctly.', 'ts-any-captcha') . '</p>',
					__('Comment Submission Failure.', 'ts-any-captcha'),
					array(
						'response'  => $data,
						'back_link' => true,
					)
				);
			}
			
			if(!$tsac_image_captcha->verify($_POST['tsac_image_captcha'])){
				$tsac_image_captcha->new_captcha();
				wp_die(
					'<p>' . __('Solve imageCaptcha correctly.', 'ts-any-captcha') . '</p>',
					__('Comment Submission Failure.', 'ts-any-captcha'),
					array(
						'response'  => $data,
						'back_link' => true,
					)
				);
			}
		}
	}
}
add_action('pre_comment_on_post', 'tsac_pre_comment_on_post', 10, 1);

if (in_array('woocommerce/woocommerce.php', get_option('active_plugins'))){
	add_action('woocommerce_resetpassword_form',  'tsac_woocommerce_display_captcha');
	add_action('woocommerce_lostpassword_form',  'tsac_woocommerce_display_captcha');
	add_action('woocommerce_login_form',  'tsac_woocommerce_display_captcha');
	add_action('woocommerce_register_form',  'tsac_woocommerce_register_display_captcha');
	function tsac_woocommerce_display_captcha(){
		$options = get_option('tsac_options');

		if(isset($options['tsac_grecaptcha_enable'])){
			if($options['tsac_grecaptcha_enable'] == "true"){
				?>
				<script>
					var onloadCallback = function() {
						recaptcha = grecaptcha.render(document.getElementById('recaptcha'), {
							'sitekey' : '<?php echo $options['tsac_grecaptcha_site_key']; ?>'
						});
					};
				</script>
				<div id="recaptcha" class="recaptcha"></div>
				<script src="https://www.google.com/recaptcha/api.js?onload=onloadCallback"></script>

				<?php
			}
		}

		if(isset($options['tsac_mathcaptcha_enable'])){
			if($options['tsac_mathcaptcha_enable'] == "true"){
				$tsac_math_captcha = MathCaptcha::get_instance();
				?>

				<div>
					<p>
						<?php echo __("Solve the equation: ", "ts-any-captcha") . $tsac_math_captcha->get_captcha_text() . " = "; ?>
					</p>
					<input type="number" name="tsac_math_captcha" />
				</div>

				<?php
			}
		}
		
		if(isset($options['tsac_imagecaptcha_enable'])){
			if($options['tsac_imagecaptcha_enable'] == "true"){
				$tsac_image_captcha = ImageCaptcha::get_instance();
				?>

				<div class="image-captcha">
					<p class="ic-title">
						<?php echo sprintf(__("Prove you're human by choosing the %s icon: ", "ts-any-captcha"), "<span class='selected-icon-name'>" . $tsac_image_captcha->get_captcha_result_name() . "</span>"); ?>
					</p>
					<?php echo $tsac_image_captcha->get_captcha_selected_icons(); ?>
				</div>

				<?php
			}
		}
	}
	function tsac_woocommerce_register_display_captcha(){
		$options = get_option('tsac_options');

		if(isset($options['tsac_grecaptcha_enable'])){
			if($options['tsac_grecaptcha_enable'] == "true"){
				?>
				<script>
					var onloadCallback2 = function() {
						recaptcha2 = grecaptcha.render(document.getElementById('recaptcha2'), {
							'sitekey' : '<?php echo $options['tsac_grecaptcha_site_key']; ?>'
						});
					};
				</script>
				<div id="recaptcha2" class="recaptcha"></div>
				<script src="https://www.google.com/recaptcha/api.js?onload=onloadCallback2"></script>

				<?php
			}
		}

		if(isset($options['tsac_mathcaptcha_enable'])){
			if($options['tsac_mathcaptcha_enable'] == "true"){
				$tsac_math_captcha = MathCaptcha::get_instance();
				?>

				<div>
					<p>
						<?php echo __("Solve the equation: ", "ts-any-captcha") . $tsac_math_captcha->get_captcha_text() . " = "; ?>
					</p>
					<input type="number" name="tsac_math_captcha" />
				</div>

				<?php
			}
		}
		
		if(isset($options['tsac_imagecaptcha_enable'])){
			if($options['tsac_imagecaptcha_enable'] == "true"){
				$tsac_image_captcha = ImageCaptcha::get_instance();
				?>

				<div class="image-captcha">
					<p class="ic-title">
						<?php echo sprintf(__("Prove you're human by choosing the %s icon: ", "ts-any-captcha"), "<span class='selected-icon-name'>" . $tsac_image_captcha->get_captcha_result_name() . "</span>"); ?>
					</p>
					<?php echo $tsac_image_captcha->get_captcha_selected_icons(); ?>
				</div>

				<?php
			}
		}
	}
	
	function tsac_woocommerce_process_registration_errors($validation_error, $login, $password, $email){
		$options = get_option('tsac_options');
		
		if(isset($options['tsac_grecaptcha_enable'])){
			if($options['tsac_grecaptcha_enable'] == "true"){
				$response = wp_remote_post('https://www.google.com/recaptcha/api/siteverify', [
					'body' => [
						'secret'   => $options['tsac_grecaptcha_secret_key'],
						'response' => $_POST['g-recaptcha-response'],
						'remoteip' => $_SERVER['REMOTE_ADDR']
					]
				]);

				$response_code = wp_remote_retrieve_response_code($response);
				$response_body = wp_remote_retrieve_body($response);

				if($response_code != 200)
					$validation_error->add('invalid_captcha', __('Solve Google reCaptcha correctly.', 'ts-any-captcha'));

				$result = json_decode($response_body, true);

				if(!$result['success'])
					$validation_error->add('invalid_captcha', __('Solve Google reCaptcha correctly.', 'ts-any-captcha'));

			}
		}

		if(isset($options['tsac_mathcaptcha_enable'])){
			if($options['tsac_mathcaptcha_enable'] == "true"){
				if(empty($_POST['tsac_math_captcha']))
					$validation_error->add('invalid_captcha', __('Solve mathCaptcha correctly.', 'ts-any-captcha'));

				$tsac_math_captcha = MathCaptcha::get_instance();

				if(!$tsac_math_captcha->verify($_POST['tsac_math_captcha']))
					$validation_error->add('invalid_captcha', __('Solve mathCaptcha correctly.', 'ts-any-captcha'));
				
				$tsac_math_captcha->new_captcha();
			}
		}
		
		
		if(isset($options['tsac_imagecaptcha_enable'])){
			if($options['tsac_imagecaptcha_enable'] == "true"){
				if(empty($_POST['tsac_image_captcha']))
					$validation_error->add('invalid_captcha', __('Solve imageCaptcha correctly.', 'ts-any-captcha'));

				$tsac_image_captcha = ImageCaptcha::get_instance();

				if(!$tsac_image_captcha->verify($_POST['tsac_image_captcha']))
					$validation_error->add('invalid_captcha', __('Solve imageCaptcha correctly.', 'ts-any-captcha'));
				
				$tsac_image_captcha->new_captcha();
			}
		}
		return $validation_error;
	}
	add_action('woocommerce_process_registration_errors', 'tsac_woocommerce_process_registration_errors', 10, 4);
	
}

if (in_array('elementor-pro/elementor-pro.php', get_option('active_plugins'))){
	function tsac_elementor_render_math_captcha($item, $item_index, $form){
		$options = get_option('tsac_options');
		
		if(isset($options['tsac_mathcaptcha_enable'])){
			if($options['tsac_mathcaptcha_enable'] == "true"){
				$tsac_math_captcha = MathCaptcha::get_instance();

				$item['field_label'] = __('Solve the equation: ', 'ts-any-captcha') . $tsac_math_captcha->get_captcha_text() . " =";
			}
		}
		return $item;
	}
	add_filter('elementor_pro/forms/render/item/math-captcha', 'tsac_elementor_render_math_captcha', 10, 3);
}


if (in_array('contact-form-7/wp-contact-form-7.php', get_option('active_plugins'))){
	
	add_action( 'wpcf7_admin_init', 'tsac_wpcf7_add_tag_generator');
	add_action( 'wpcf7_init', 'tsac_anycaptcha' );	
	add_filter( 'wpcf7_validate_tsac_grecaptcha', 'tsac_anycaptcha_wpcf7_validation', 10, 2 );
	add_filter( 'wpcf7_validate_tsac_mathcaptcha', 'tsac_anycaptcha_wpcf7_validation', 10, 2 );
	add_filter( 'wpcf7_validate_tsac_imagecaptcha', 'tsac_anycaptcha_wpcf7_validation', 10, 2 );
	
	
	function tsac_wpcf7_add_tag_generator() {
		$tag_generator = WPCF7_TagGenerator::get_instance();
		
		$options = get_option('tsac_options');
		if(isset($options['tsac_grecaptcha_enable'])){
			if($options['tsac_grecaptcha_enable'] == "true"){
				$tag_generator->add('tsac_grecaptcha', __('reCaptcha', 'ts-any-captcha'), 'tsac_wpcf7_tag_generator');
			}
		}
		
		if(isset($options['tsac_mathcaptcha_enable'])){
			if($options['tsac_mathcaptcha_enable'] == "true"){
				$tag_generator->add('tsac_mathcaptcha', __('mathCaptcha', 'ts-any-captcha'), 'tsac_wpcf7_tag_generator');
			}
		}
		
		if(isset($options['tsac_imagecaptcha_enable'])){
			if($options['tsac_imagecaptcha_enable'] == "true"){
				$tag_generator->add('tsac_imagecaptcha', __('imageCaptcha', 'ts-any-captcha'), 'tsac_wpcf7_tag_generator');
			}
		}
	}

	function tsac_wpcf7_tag_generator($contact_form, $args = '') {
		$args = wp_parse_args($args, array());
		$type = $args['id'];
		?>
		<div class="insert-box">
			<input type="text" name="<?php echo $type; ?>" class="tag code" readonly="readonly" onfocus="this.select()" />
			<div class="submitbox">
				<input type="button" class="button button-primary insert-tag" value="<?php echo __('Insert Tag', 'ts-any-captcha'); ?>" />
			</div>
		</div>
		<?php
	}


	function tsac_anycaptcha() {
		$options = get_option('tsac_options');
		
		if(isset($options['tsac_grecaptcha_enable'])){
			if($options['tsac_grecaptcha_enable'] == "true"){
				wpcf7_add_form_tag(
					'tsac_grecaptcha',
					'tsac_anycaptcha_handler', 
					array( 
						'name-attr' => true 
					)
				);
			}
		}
		
		if(isset($options['tsac_mathcaptcha_enable'])){
			if($options['tsac_mathcaptcha_enable'] == "true"){
				wpcf7_add_form_tag(
					'tsac_mathcaptcha',
					'tsac_anycaptcha_handler', 
					array( 
						'name-attr' => true 
					)
				);
			}
		}
		
		if(isset($options['tsac_imagecaptcha_enable'])){
			if($options['tsac_imagecaptcha_enable'] == "true"){
				wpcf7_add_form_tag(
					'tsac_imagecaptcha',
					'tsac_anycaptcha_handler', 
					array( 
						'name-attr' => true 
					)
				);
			}
		}
	}

	function tsac_anycaptcha_wpcf7_validation($wpcf7_result, $tag){
		$options = get_option('tsac_options');
		
		if($tag['type'] == 'tsac_grecaptcha'){
			$tag = array('type' => 'tsac_grecaptcha', 'name' => 'tsac_grecaptcha');
			
			$response = wp_remote_post('https://www.google.com/recaptcha/api/siteverify', [
				'body' => [
					'secret'   => $options['tsac_grecaptcha_secret_key'],
					'response' => $_POST['g-recaptcha-response'],
					'remoteip' => $_SERVER['REMOTE_ADDR']
				]
			]);

			$response_code = wp_remote_retrieve_response_code($response);
			$response_body = wp_remote_retrieve_body($response);

			if($response_code != 200)
				$wpcf7_result->invalidate($tag, __('Solve Google reCaptcha correctly.', 'ts-any-captcha'));

			$result = json_decode($response_body, true);

			if(!$result['success'])
				$wpcf7_result->invalidate($tag, __('Solve Google reCaptcha correctly.', 'ts-any-captcha'));

		}
		
		if($tag['type'] == 'tsac_mathcaptcha'){
			$tag = array('type' => 'tsac_mathcaptcha', 'name' => 'tsac_math_captcha');
			
			if(empty($_POST['tsac_math_captcha']))
				$wpcf7_result->invalidate($tag, __('Solve mathCaptcha correctly.', 'ts-any-captcha'));

			$tsac_math_captcha = MathCaptcha::get_instance();

			if(!$tsac_math_captcha->verify($_POST['tsac_math_captcha']))
				$wpcf7_result->invalidate($tag, __('Solve mathCaptcha correctly.', 'ts-any-captcha'));
		}
		
		if($tag['type'] == 'tsac_imagecaptcha'){
			$tag = array('type' => 'tsac_imagecaptcha', 'name' => 'tsac_image_captcha');
			
			if(empty($_POST['tsac_image_captcha']))
				$wpcf7_result->invalidate($tag, __('Solve imageCaptcha correctly.', 'ts-any-captcha'));

			$tsac_image_captcha = ImageCaptcha::get_instance();

			if(!$tsac_image_captcha->verify($_POST['tsac_image_captcha']))
				$wpcf7_result->invalidate($tag, __('Solve imageCaptcha correctly.', 'ts-any-captcha'));
		}

		return $wpcf7_result;
	}

	function tsac_anycaptcha_handler( $tag ) {
		$options = get_option('tsac_options');

		$any_captcha_fields = "";
		
		if($tag->type == "tsac_grecaptcha"){
			
			$validation_error = wpcf7_get_validation_error( "tsac_grecaptcha" );
			
			ob_start();
			?>
			<script>
				var tsac_wpcf_recaptcha;
				var onloadCallback = function() {
					tsac_wpcf_recaptcha = grecaptcha.render(document.getElementById('recaptcha'), {
						'sitekey' : '<?php echo $options['tsac_grecaptcha_site_key']; ?>'
					});
				};
			</script>
			<div id="recaptcha" class="recaptcha"></div>
			<span class="wpcf7-form-control-wrap tsac_grecaptcha" data-name="tsac_grecaptcha"><?php echo $validation_error; ?></span>
			<script src="https://www.google.com/recaptcha/api.js?onload=onloadCallback"></script>
			<script>
				window.addEventListener("load", (event) => {
					document.querySelector(".wpcf7-submit").addEventListener("click", (event) => {
						const myTimeout = setTimeout(function(){
							grecaptcha.reset(tsac_wpcf_recaptcha);
						}, 2000);
					});
				});
			</script>

			<?php
			$any_captcha_fields .= ob_get_clean();
		}
		
		if($tag->type == "tsac_mathcaptcha"){
			$tsac_math_captcha = MathCaptcha::get_instance();
			
			$validation_error = wpcf7_get_validation_error( "tsac_math_captcha" );
			
			ob_start();
				?>

				<div>
					<p>
						<?php echo __("Solve the equation: ", "ts-any-captcha") . $tsac_math_captcha->get_captcha_text() . " = "; ?>
					</p>
					<input type="number" aria-invalid="<?php echo !empty($validation_error)?'true':'false'; ?>" class="<?php echo !empty($validation_error)?'wpcf7-not-valid':''; ?>" name="tsac_math_captcha" />
					<input type="hidden" name="tsac_math_captcha_result" value="<?php echo hash_hmac('md5', $tsac_math_captcha->get_captcha_result(), get_option('tsac_hash_key')); ?>">
					<span class="wpcf7-form-control-wrap tsac_math_captcha" data-name="tsac_math_captcha"><?php echo $validation_error; ?></span>
				</div>

				<?php
			$any_captcha_fields .= ob_get_clean();
		}
		
		if($tag->type == "tsac_imagecaptcha"){
			$tsac_image_captcha = ImageCaptcha::get_instance();
			
			$validation_error = wpcf7_get_validation_error( "tsac_image_captcha" );
			
			ob_start();
				?>

				<div class="image-captcha">
					<p class="ic-title">
						<?php echo sprintf(__("Prove you're human by choosing the %s icon: ", "ts-any-captcha"), "<span class='selected-icon-name'>" . $tsac_image_captcha->get_captcha_result_name() . "</span>"); ?>
					</p>
					<?php echo $tsac_image_captcha->get_captcha_selected_icons(); ?>
					<input type="hidden" name="tsac_image_captcha_result" value="<?php echo hash_hmac('md5', $tsac_image_captcha->get_captcha_result(), get_option('tsac_hash_key')); ?>">
					<span class="wpcf7-form-control-wrap tsac_image_captcha" data-name="tsac_image_captcha"><?php echo $validation_error; ?></span>
				</div>

				<?php
			$any_captcha_fields .= ob_get_clean();
		}
		
		return $any_captcha_fields;
	}
	
}
