<?php
/**
 * Name       : Habakiri Share Buttons Option
 * Version    : 1.0.0
 * Author     : Takashi Kitajima
 * Author URI : http://2inc.org
 * Create     : June 15, 2015
 * Modified   :
 * License    : GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class Habakiri_Share_Buttons_Option {

	/**
	 * シェアボタンの種類
	 * @var array
	 */
	protected static $type = array(
		'balloon'    => 'Balloon',
		'horizontal' => 'Horizontal',
	);

	/**
	 * 表示位置
	 * @var array
	 */
	protected static $position = array(
		'habakiri_before_entry_content' => 'Before Content',
		'habakiri_after_entry_content'  => 'After Content',
	);

	/**
	 * 表示する投稿タイプ
	 * @var array
	 */
	protected static $post_type = array();

	/**
	 * 選択肢を返す
	 *
	 * @param string $key name 属性
	 * @return array
	 */
	public static function get_choices( $key ) {
		if ( $key === 'post_type' ) {
			$post_types = get_post_types( array(
				'public' => true,
			), 'objects' );
			$validated_post_types = array();
			foreach ( $post_types as $post_type ) {
				$validated_post_types[$post_type->name] = $post_type->labels->name;
			}
			unset( $validated_post_types['attachment'] );
			self::$$key = $validated_post_types;
		}

		if ( !empty( self::$$key ) ) {
			return self::$$key;
		}
	}

	/**
	 * 選択肢の最初のキーを返す
	 *
	 * @param string $key name 属性
	 * @return string|null
	 */
	public static function get_first_value( $key ) {
		$choices = self::get_choices( $key );
		if ( !empty( $choices ) ) {
			reset( $choices );
			return key( $choices );
		}
	}

	/**
	 * 保存された値を返す
	 *
	 * @param string $key name 属性
	 * @return array|string|false|null
	 */
	public static function get( $key ) {
		$options = get_option( Habakiri_Share_Buttons_Config::NAME );
		$choices = self::get_choices( $key );

		// 設定値が存在しないとき
		if ( empty( $choices ) ) {
			return;
		}

		// 保存されていないとき
		if ( !array_key_exists( $key, $options ) ) {
			return;
		}

		$option = $options[$key];

		// 選択肢が文字列のときは、値が文字列なら返す
		if ( !is_array( $choices ) ) {
			if ( is_array( $option ) ) {
				return;
			}
			return $option;
		}

		// 選択肢が配列のとき

		// 値が文字列のときは、選択肢にその値があれば返す
		if ( !is_array( $option ) ) {
			if ( !array_key_exists( $option, $choices ) ) {
				return;
			}
			return $option;
		}

		// 値が配列のとき
		$validate_option = array();
		foreach ( $option as $option_key => $option_value ) {
			if ( array_key_exists( $option_value, $choices ) ) {
				$validate_option[$option_key] = $option_value;
			}
		}
		return $validate_option;
	}
}
