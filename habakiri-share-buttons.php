<?php
/**
 * Plugin Name: Habakiri Share Buttons
 * Plugin URI: https://github.com/inc2734/habakiri-share-buttons
 * Description: Add social share buttons on Habakiri theme.
 * Version: 1.3.1
 * Author: Takashi Kitajima
 * Author URI: http://2inc.org
 * Created : June 15, 2015
 * Modified: October 13, 2015
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

		$feedly_action = Habakiri_Share_Buttons_Config::KEY . '_feedly';
		add_action( 'wp_ajax_' . $feedly_action       , array( $this, 'get_feedly' ) );
		add_action( 'wp_ajax_nopriv_' . $feedly_action, array( $this, 'get_feedly' ) );

		$pocket_action = Habakiri_Share_Buttons_Config::KEY . '_pocket';
		add_action( 'wp_ajax_' . $pocket_action      , array( $this, 'get_pocket' ) );
		add_action('wp_ajax_nopriv_' . $pocket_action, array( $this, 'get_pocket' ) );

		$position = Habakiri_Share_Buttons_Option::get( 'position' );
		if ( is_array( $position ) ) {
			foreach ( $position as $value ) {
				add_action( $value, array( $this, 'display_share_buttons' ) );
			}
		}
		
		add_shortcode( Habakiri_Share_Buttons_Config::KEY, array( $this, 'shortcode' ) );
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

		$feedly_action = Habakiri_Share_Buttons_Config::KEY . '_feedly';
		wp_localize_script(
			Habakiri_Share_Buttons_Config::NAME,
			$feedly_action,
			array(
				'endpoint'    => admin_url( 'admin-ajax.php' ),
				'action'      => $feedly_action,
				'_ajax_nonce' => wp_create_nonce( $feedly_action )
			)
		);
		
		$pocket_action = Habakiri_Share_Buttons_Config::KEY . '_pocket';
		wp_localize_script(
			Habakiri_Share_Buttons_Config::NAME,
			$pocket_action,
			array(
				'endpoint'    => admin_url( 'admin-ajax.php' ),
				'action'      => $pocket_action,
				'_ajax_nonce' => wp_create_nonce( $pocket_action ),
			)
		);
	}

	/**
	 * Feedly の講読者数を json として出力
	 */
	public function get_feedly() {
		$feedly_action = Habakiri_Share_Buttons_Config::KEY . '_feedly';
		check_ajax_referer( $feedly_action );

		$feed_url = rawurlencode( get_bloginfo( 'rss2_url' ) );
		$response = wp_remote_get( "http://cloud.feedly.com/v3/feeds/feed%2F$feed_url" );
		$body = wp_remote_retrieve_body( $response );
		wp_send_json( json_decode( $body ) );
	}

	/**
	 * Pocket のブックマーク数を json として出力
	 */
	public function get_pocket() {
		$pocket_action = Habakiri_Share_Buttons_Config::KEY . '_pocket';
		check_ajax_referer( $pocket_action );

		if ( empty( $_GET['post_id'] ) ) {
			return 0;
		}

		$post_id = $_GET['post_id'];
		$url = rawurlencode( get_permalink( $post_id ) );
		$response = wp_remote_get( "https://widgets.getpocket.com/v1/button?count=vertical&url=$url" );
		$body = wp_remote_retrieve_body( $response );
		preg_match( '/<em id="cnt">(\d*?)<\/em>/', $body, $reg );

		$count = 0;
		if ( !empty( $reg[1] ) ) {
			$count = $reg[1];
		}
		wp_send_json( $count );
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
			'<div id="habakiri-share-buttons-%1$d" class="habakiri-share-buttons habakiri-share-buttons-%2$s" data-habakiri-share-buttons-title="%3$s" data-habakiri-share-buttons-url="%4$s" data-habakiri-share-buttons-postid="%1$s">
				<ul>
					<li class="habakiri-share-buttons-facebook">
						<div class="habakiri-share-buttons-count">0</div>
						<a class="habakiri-share-buttons-button" href="https://www.facebook.com/sharer/sharer.php?u=%4$s" target="_blank">
							<span class="genericon genericon-facebook"></span>
							%6$s
						</a>
					</li>
					<li class="habakiri-share-buttons-twitter">
						<div class="habakiri-share-buttons-count">-</div>
						<a class="habakiri-share-buttons-button" href="https://twitter.com/share?&amp;text=%3$s&amp;url=%4$s" target="_blank">
							<span class="genericon genericon-twitter"></span>
							%7$s
						</a>
					</li>
					<li class="habakiri-share-buttons-hatena">
						<div class="habakiri-share-buttons-count">0</div>
						<a class="habakiri-share-buttons-button" href="http://b.hatena.ne.jp/add?mode=confirm&amp;url=%4$s&amp;title=%3$s" target="_blank">
							<span class="genericon habakiri-share-buttons-icon habakiri-share-buttons-icon-hatena"></span>
							%8$s
						</a>
					</li>
					<li class="habakiri-share-buttons-pocket">
						<div class="habakiri-share-buttons-count">0</div>
						<a class="habakiri-share-buttons-button" href="http://getpocket.com/edit?url=%4$s&title=%3$s" target="_blank">
							<span class="genericon genericon-pocket"></span>
							%9$s
						</a>
					</li>
					<li class="habakiri-share-buttons-feedly">
						<div class="habakiri-share-buttons-count">0</div>
						<a class="habakiri-share-buttons-button" href="http://feedly.com/index.html#subscription/feed/%5$s" target="_blank">
							<span class="genericon habakiri-share-buttons-icon habakiri-share-buttons-icon-feedly"></span>
							%10$s
						</a>
					</li>
				</ul>
			</div>',
			get_the_ID(),
			esc_attr( $type ),
			esc_attr( $title ),
			esc_attr( $permalink ),
			get_bloginfo( 'rss2_url' ),
			esc_html__( 'Like!', 'habakiri-share-buttons' ),
			esc_html__( 'Tweet', 'habakiri-share-buttons' ),
			esc_html__( 'Bookmark', 'habakiri-share-buttons' ),
			esc_html__( 'Pocket', 'habakiri-share-buttons' ),
			esc_html__( 'Feedly', 'habakiri-share-buttons' )
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
			'[%s type="%s"]',
			Habakiri_Share_Buttons_Config::KEY,
			$type
		) );
	}
}

$theme = wp_get_theme();
if ( $theme->template === 'habakiri' ) {
	$Habakiri_Share_Buttons = new Habakiri_Share_Buttons();
}
