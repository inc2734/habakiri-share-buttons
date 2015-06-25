<?php
/**
 * Plugin Name: Habakiri Share Buttons
 * Plugin URI: https://github.com/inc2734/habakiri-share-buttons
 * Description: Add social share buttons on Habakiri theme.
 * Version: 1.0.0
 * Author: Takashi Kitajima
 * Author URI: http://2inc.org
 * Created : June 15, 2015
 * Modified: 
 * Text Domain: habakiri-share-buttons
 * Domain Path: /languages/
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
include_once( plugin_dir_path( __FILE__ ) . 'classes/class.config.php' );
include_once( plugin_dir_path( __FILE__ ) . 'classes/class.option.php' );
include_once( plugin_dir_path( __FILE__ ) . 'classes/class.habakiri-share-buttons-settings.php' );
include_once( plugin_dir_path( __FILE__ ) . 'classes/class.github-updater.php' );
new habakiri_Plugin_GitHub_Updater( 'habakiri-share-buttons', __FILE__, 'inc2734' );

class Habakiri_Share_Buttons {

	/**
	 * __construct
	 */
	public function __construct() {
		register_uninstall_hook( __FILE__, array( __CLASS__, 'uninstall' ) );
		add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );
		add_action( 'init'          , array( $this, 'init' ) );
	}

	/**
	 * アンインストール時の処理
	 */
	public static function uninstall() {
		delete_option( Habakiri_Share_Buttons_Config::NAME );
	}

	/**
	 * 言語ファイルの読み込み
	 */
	public function plugins_loaded() {
		load_plugin_textdomain(
			Habakiri_Share_Buttons_Config::NAME,
			false,
			basename( dirname( __FILE__ ) ) . '/languages'
		);
	}

	public function init() {
		new Habakiri_Share_Buttons_Settings();
		add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ) );

		$position = Habakiri_Share_Buttons_Option::get( 'position' );
		if ( is_array( $position ) ) {
			foreach ( $position as $value ) {
				add_action( $value, array( $this, 'display_share_buttons' ) );
			}
		}
		
		add_shortcode( 'habakiri_share_buttons', array( $this, 'shortcode' ) );
	}

	/**
	 * CSS、JS の読み込み
	 */
	public function wp_enqueue_scripts() {
		$url = plugins_url( Habakiri_Share_Buttons_Config::NAME );

		wp_enqueue_style(
			Habakiri_Share_Buttons_Config::NAME,
			$url . '/css/style.min.css'
		);
		wp_enqueue_script(
			Habakiri_Share_Buttons_Config::NAME,
			$url . '/js/app.min.js',
			array( 'jquery' ),
			null,
			false
		);
	}

	/**
	 * シェアボタンを表示するショートコード
	 *
	 * @param array $attributes
	 * @return string
	 */
	public function shortcode( $attributes ) {
		$attributes = shortcode_atts( array(
			'type'      => 'balloon',
			'title'     => get_the_title(),
			'permalink' => get_permalink(),
		), $attributes );

		$type      = $attributes['type'];
		$title     = urlencode( esc_attr( $attributes['title'] ) );
		$permalink = urlencode( esc_attr( $attributes['permalink'] ) );

		return sprintf(
			'<div id="habakiri-share-buttons-%1$d" class="habakiri-share-buttons habakiri-share-buttons-%2$s" data-habakiri-share-buttons-title="%3$s" data-habakiri-share-buttons-url="%4$s">
				<ul>
					<li class="habakiri-share-buttons-facebook">
						<div class="habakiri-share-buttons-count">0</div>
						<a class="habakiri-share-buttons-button" href="https://www.facebook.com/sharer/sharer.php?u=%4$s" target="_blank">
							<span class="genericon genericon-facebook"></span>
							%5$s
						</a>
					</li>
					<li class="habakiri-share-buttons-twitter">
						<div class="habakiri-share-buttons-count">0</div>
						<a class="habakiri-share-buttons-button" href="https://twitter.com/share?&amp;text=%3$s&amp;url=%4$s" target="_blank">
							<span class="genericon genericon-twitter"></span>
							%6$s
						</a>
					</li>
					<li class="habakiri-share-buttons-hatena">
						<div class="habakiri-share-buttons-count">0</div>
						<a class="habakiri-share-buttons-button" href="http://b.hatena.ne.jp/add?mode=confirm&amp;url=%4$s&amp;title=%3$s" target="_blank">
							<span class="genericon habakiri-share-buttons-icon habakiri-share-buttons-icon-hatena"></span>
							%7$s
						</a>
					</li>
				</ul>
			</div>',
			get_the_ID(),
			esc_attr( $type ),
			esc_attr( $title ),
			esc_attr( $permalink ),
			esc_html__( 'Like!', 'habakiri-share-buttons' ),
			esc_html__( 'Tweet', 'habakiri-share-buttons' ),
			esc_html__( 'Bookmark', 'habakiri-share-buttons' )
		);
	}

	/**
	 * シェアボタンを表示（投稿ページのみ）
	 */
	public function display_share_buttons() {
		$post_type = Habakiri_Share_Buttons_Option::get( 'post_type' );
		if ( !is_array( $post_type ) || !in_array( get_post_type(), $post_type ) ) {
			return;
		}

		$type = Habakiri_Share_Buttons_Option::get( 'type' );

		echo do_shortcode( sprintf(
			'[habakiri_share_buttons type="%s"]',
			$type
		) );
	}
}

$theme = wp_get_theme();
if ( $theme->template === 'habakiri' ) {
	$Habakiri_Share_Buttons = new Habakiri_Share_Buttons();
}
