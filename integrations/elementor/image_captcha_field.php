<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Elementor_Image_Captcha_Field extends \ElementorPro\Modules\Forms\Fields\Field_Base {

	public function get_type() {
		return 'image-captcha';
	}

	public function get_name() {
		return esc_html__( 'imageCaptcha', 'ts-any-captcha' );
	}
	
	 public function render( $item, $item_index, $form ) {
		$form->add_render_attribute(
			'input' . $item_index,
			[
				'size' => '1',
				'class' => 'elementor-field-textual',
				'title' => esc_html__('Solve imageCaptcha correctly.', 'ts-any-captcha'),
			]
		);
		$tsac_image_captcha = ImageCaptcha::get_instance();
		?>

		<div class="image-captcha">
			<p class="ic-title">
				<?php echo sprintf(__("Prove you're human by choosing the %s icon: ", "ts-any-captcha"), "<span class='selected-icon-name'>" . $tsac_image_captcha->get_captcha_result_name() . "</span>"); ?>
			</p>
			<?php echo $tsac_image_captcha->get_captcha_selected_icons_for_elementor('form_fields[' . $item['custom_id'] . ']'); ?>
			<span id="form-field-<?php echo $item['custom_id']; ?>"></span>
		</div>

		<?php
		 
		//echo '<input ' . $form->get_render_attribute_string( 'input' . $item_index ) . '>';
	}
	 
	public function validation( $field, $record, $ajax_handler ) {
		if(empty($field['value']))
			$ajax_handler->add_error(
				$field['id'],
				__('Solve imageCaptcha correctly.', 'ts-any-captcha')
			);
		
		$tsac_image_captcha = ImageCaptcha::get_instance();

		if(!$tsac_image_captcha->verify($field['value']))
			$ajax_handler->add_error(
				$field['id'],
				__('Solve imageCaptcha correctly.', 'ts-any-captcha')
			);
	}

	public function __construct() {
		parent::__construct();
	}
}
