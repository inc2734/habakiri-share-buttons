<?php
/**
 * Name       : Habakiri Share Buttons Settings
 * Version    : 1.0.0
 * Author     : Takashi Kitajima
 * Author URI : http://2inc.org
 * Create     : June 15, 2015
 * Modified   :
 * License    : GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class Habakiri_Share_Buttons_Settings {

	/**
	 * __construct
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'admin_init', array( $this, 'register_setting' ) );

		$this->type = array(
			'balloon'    => __( 'Balloon', 'habakiri-share-buttons' ),
			'horizontal' => __( 'Horizontal', 'habakiri-share-buttons' ),
		);
	}

	/**
	 * 管理メニューに追加
	 */
	public function admin_menu() {
		$hook = add_options_page(
			__( 'Share Buttons Settings', 'habakiri-share-buttons' ),
			__( 'Share Buttons Settings', 'habakiri-share-buttons' ),
			'manage_options',
			basename( __FILE__ ),
			array( $this, 'display' )
		);
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
	}

	/**
	 * CSS・JS の読み込み
	 *
	 * @param string $hook
	 */
	public function admin_enqueue_scripts( $hook ) {
		if ( $hook !== 'settings_page_class.habakiri-share-buttons-settings' ) {
			return;
		}

		$url = plugins_url( Habakiri_Share_Buttons_Config::NAME );
		wp_enqueue_style(
			Habakiri_Share_Buttons_Config::NAME . '-settings',
			$url . '/css/settings.css'
		);
		/*
		wp_enqueue_script(
			Habakiri_Share_Buttons_Config::NAME . '-settings',
			$url . '/js/settings.js',
			array( 'jquery' ),
			false,
			true
		);
		*/
	}

	/**
	 * name 属性が habakiri-share-buttons の項目だけ許可。
	 * さらに $this->valdidate でフィルタリング
	 */
	public function register_setting() {
		register_setting(
			Habakiri_Share_Buttons_Config::NAME . '-group',
			Habakiri_Share_Buttons_Config::NAME,
			array( $this, 'validate' )
		);
	}

	public function validate( $values ) {
		// type
		$type = Habakiri_Share_Buttons_Option::get_choices( 'type' );
		if ( array_key_exists( 'type', $values ) ) {
			if ( is_array( $values['type'] ) || !array_key_exists( $values['type'], $type ) ) {
				unset( $values['type'] );
			}
		}

		// position
		$position = Habakiri_Share_Buttons_Option::get_choices( 'position' );
		if ( array_key_exists( 'position', $values ) ) {
			if ( !is_array( $values['position'] ) ) {
				unset( $values['position'] );
			} else {
				foreach ( $values['position'] as $key => $value ) {
					if ( !array_key_exists( $value, $position ) ) {
						unset( $values['position'][$key] );
					}
				}
			}
		}

		// post_type
		$post_type = Habakiri_Share_Buttons_Option::get_choices( 'post_type' );
		if ( array_key_exists( 'post_type', $values ) ) {
			if ( !is_array( $values['post_type'] ) ) {
				unset( $values['post_type'] );
			} else {
				foreach ( $values['post_type'] as $key => $value ) {
					if ( !array_key_exists( $value, $post_type ) ) {
						unset( $values['post_type'][$key] );
					}
				}
			}
		}

		return $values;
	}

	/**
	 * 管理画面を表示
	 */
	public function display() {
		?>
		<div class="wrap">
			<h2><?php esc_html_e( 'Share Buttons Settings', 'habakiri-share-buttons' ); ?></h2>

			<form method="post" action="options.php">
				<?php settings_fields( Habakiri_Share_Buttons_Config::NAME . '-group' ); ?>
				<table class="form-table">
					<tr>
						<th><?php esc_html_e( 'Share Buttons Type', 'habakiri-share-buttons' ); ?></th>
						<td>
							<?php
							$choices = Habakiri_Share_Buttons_Option::get_choices( 'type' );
							$type    = Habakiri_Share_Buttons_Option::get( 'type' );
							if ( is_null( $type ) ) {
								$type = Habakiri_Share_Buttons_Option::get_first_value( 'type' );
							}
							?>
							<ul>
								<?php foreach ( $choices as $key => $value ) : ?>
								<li><label><input type="radio" name="<?php echo esc_attr( Habakiri_Share_Buttons_Config::NAME ); ?>[type]" value="<?php echo esc_attr( $key ); ?>" <?php checked( $type, $key ); ?> /> <?php esc_html_e( $value, 'habakiri-share-buttons' ); ?></label></li>
								<?php endforeach; ?>
							</ul>
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Position to Display', 'habakiri-share-buttons' ); ?></th>
						<td>
							<?php
							$choices  = Habakiri_Share_Buttons_Option::get_choices( 'position' );
							$position = Habakiri_Share_Buttons_Option::get( 'position' );
							if ( is_null( $position ) ) {
								$position = array();
							}
							?>
							<ul>
								<?php foreach ( $choices as $key => $value ) : ?>
								<li><label><input type="checkbox" name="<?php echo esc_attr( Habakiri_Share_Buttons_Config::NAME ); ?>[position][]" value="<?php echo esc_attr( $key ); ?>" <?php checked( in_array( $key, $position ) ); ?> /> <?php esc_html_e( $value, 'habakiri-share-buttons' ); ?></label></li>
								<?php endforeach; ?>
							</ul>
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Post Types to Display', 'habakiri-share-buttons' ); ?></th>
						<td>
							<?php
							$choices   = Habakiri_Share_Buttons_Option::get_choices( 'post_type' );
							$post_type = Habakiri_Share_Buttons_Option::get( 'post_type' );
							if ( is_null( $post_type ) ) {
								$post_type = array();
							}
							?>
							<ul>
								<?php foreach ( $choices as $key => $value ) : ?>
								<li><label><input type="checkbox" name="<?php echo esc_attr( Habakiri_Share_Buttons_Config::NAME ); ?>[post_type][]" value="<?php echo esc_attr( $key ); ?>" <?php checked( in_array( $key, $post_type ) ); ?> /> <?php esc_html_e( $value, 'habakiri-share-buttons' ); ?></label></li>
								<?php endforeach; ?>
							</ul>
						</td>
					</tr>
				</table>
				<p class="submit">
					<input type="submit" class="button-primary" value="<?php esc_html_e( 'Save Changes', 'habakiri-share-buttons' ) ?>" />
				</p>
			</form>
		<!-- end .wrap --></div>
		<?php
	}
}
