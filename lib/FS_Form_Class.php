<?php
/**
 * Created by PhpStorm.
 * User: karak
 * Date: 27.02.2017
 * Time: 15:57
 */

namespace FS;


use ES_LIB\ES_config;

class FS_Form_Class {

	/**
	 * @param string $field_name ключ поля в FS_Config::$form_fields
	 * @param array $args атрибуты input (class,id,value,checked)
	 *
	 * @return string html код поля
	 */
	function fs_form_field( $field_name, $args = array() ) {
		global $fs_config;
		$default     = array(
			'type'        => 'text',
			'class'       => '',
			'id'          => str_replace( array(
				'[',
				']'
			), array( '_' ), $field_name ),
			'required'    => false,
			'title'       => '',
			'placeholder' => '',
			'value'       => '',
			'editor_args' => array( 'textarea_rows' => 8, 'textarea_name' => $field_name )

		);
		$args        = wp_parse_args( $args, $default );
		$class       = ! empty( $args['class'] ) ? 'class="' . sanitize_html_class( $args['class'] ) . '"' : '';
		$id          = ! empty( $args['id'] ) ? 'id="' . sanitize_html_class( $args['id'] ) . '"' : 'id=""';
		$title       = ! empty( $args['title'] ) ? 'title="' . esc_html( $args['title'] ) . '"' : '';
		$placeholder = ! empty( $args['placeholder'] ) ? 'placeholder="' . esc_html( $args['placeholder'] ) . '"' : '';
		$value       = ! empty( $args['value'] ) ? 'value="' . esc_html( $args['value'] ) . '"' : '';

		$required = ! empty( $args['required'] ) ? 'required' : '';
		$field    = '';
		switch ( $args['type'] ) {
			case 'text':
				$field = ' <input type="text" name="' . $field_name . '"  ' . $class . ' ' . $title . ' ' . $required . ' ' . $placeholder . ' ' . $value . ' ' . $id . '> ';
				break;
			case 'email':
				$field = ' <input type="email" name="' . $field_name . '"  ' . $class . ' ' . $title . ' ' . $required . '  ' . $placeholder . ' ' . $value . ' ' . $id . '> ';
				break;
			case 'tel':
				$field = ' <input type="tel" name="' . $field_name . '"  ' . $class . ' ' . $title . ' ' . $required . '  ' . $placeholder . ' ' . $value . ' ' . $id . '> ';
				break;
			case 'radio':
				$field = ' <input type="radio" name="' . $field_name . '"  ' . checked( 'on', $value, false ) . ' ' . $class . ' ' . $title . ' ' . $required . '  ' . $placeholder . ' ' . $value . ' ' . $id . '> ';
				break;
			case 'checkbox':
				$field = ' <input type="checkbox" name="' . $field_name . '"  ' . checked( '1', $args['value'], false ) . ' ' . $class . ' ' . $title . ' ' . $required . '  ' . $placeholder . '  value="1"  ' . $id . '> ';
				break;
			case 'textarea':
				$field = '<textarea name="' . $field_name . '"  ' . $class . ' ' . $title . ' ' . $required . '  ' . $placeholder . ' ' . $id . '></textarea>';
				break;
			case 'pages':
				$field = wp_dropdown_pages( array(
					'name'     => $field_name,
					'echo'     => 0,
					'id'       => $args['id'],
					'selected' => $args['value']
				) );
				break;
			case 'pay_methods':
				$field = wp_dropdown_categories( array(
					'show_option_all' => 'Способ оплаты',
					'hide_empty'      => 0,
					'name'            => $field_name,
					'class'           => $args['class'],
					'echo'            => 0,
					'taxonomy'        => $fs_config->data['product_pay_taxonomy']
				) );
				break;
				case 'del_methods':
				$field = wp_dropdown_categories( array(
					'show_option_all' => 'Способ доставки',
					'hide_empty'      => 0,
					'name'            => $field_name,
					'class'           => $args['class'],
					'echo'            => 0,
					'taxonomy'        => $fs_config->data['product_del_taxonomy']
				) );
				break;
			case 'editor':
				wp_editor( esc_html( $args['value'] ), $args['id'], $args['editor_args'] );
				$field = ob_get_clean();
				break;
		}
		echo apply_filters( 'fs_form_field', $field, $field_name, $args );
	}
}
