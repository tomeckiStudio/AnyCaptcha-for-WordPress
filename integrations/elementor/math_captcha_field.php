<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Elementor_Math_Captcha_Field extends \ElementorPro\Modules\Forms\Fields\Field_Base {

	public function get_type() {
		return 'math-captcha';
	}

	public function get_name() {
		return esc_html__( 'mathCaptcha', 'ts-any-captcha' );
	}
	
	 public function render( $item, $item_index, $form ) {
		$form->add_render_attribute(
			'input' . $item_index,
			[
				'size' => '1',
				'class' => 'elementor-field-textual',
				'title' => esc_html__( 'Only positive and negative numbers', 'ts-any-captcha' ),
			]
		);
		
		echo '<input ' . $form->get_render_attribute_string( 'input' . $item_index ) . '>';
	}
	 
	public function validation( $field, $record, $ajax_handler ) {
		if(empty($field['value']))
			$ajax_handler->add_error(
				$field['id'],
				__('Solve mathCaptcha correctly.', 'ts-any-captcha')
			);
		
		$tsac_math_captcha = MathCaptcha::get_instance();

		if(!$tsac_math_captcha->verify($field['value']))
			$ajax_handler->add_error(
				$field['id'],
				__('Solve mathCaptcha correctly.', 'ts-any-captcha')
			);
	}

	public function __construct() {
		parent::__construct();
	}
}