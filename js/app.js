(function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);var f=new Error("Cannot find module '"+o+"'");throw f.code="MODULE_NOT_FOUND",f}var l=n[o]={exports:{}};t[o][0].call(l.exports,function(e){var n=t[o][1][e];return s(n?n:e)},l,l.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(require,module,exports){
/**
 * Version    : 1.3.0
 * Author     : Takashi Kitajima
 * Author URI : http://2inc.org
 * Create     : June 15, 2015
 * Modified   : August 20, 2016
 * License    : GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

jQuery( function( $ ) {
	$.fn.habakiri_share_buttons = function( params ) {
		var container = this;
		var params = $.extend( {
			title  : container.data( 'habakiri-share-buttons-title' ),
			url    : container.data( 'habakiri-share-buttons-url' ),
			post_id: container.data( 'habakiri-share-buttons-postid' )
		}, params );

		params.title = encodeURIComponent( params.title );

		var facebook = container.find( '.habakiri-share-buttons-facebook' );
		var twitter  = container.find( '.habakiri-share-buttons-twitter' );
		var hatena   = container.find( '.habakiri-share-buttons-hatena' );
		var pocket   = container.find( '.habakiri-share-buttons-pocket' );
		var feedly   = container.find( '.habakiri-share-buttons-feedly' );

		return container.each( function() {
			facebook_count();
			facebook_button();
			twitter_count();
			twitter_button();
			hatena_count();
			hatena_button();
			pocket_count();
			pocket_button();
			feedly_count();
		} );

		function twitter_count() {
			var api = 'https://opensharecount.com/count.json?url=' + params.url;
			$.ajax( {
				url     : api,
				dataType: 'jsonp',
				success : function( json ) {
					console.log(json);
					var count = json.count ? json.count : 0;
					twitter.find( '.habakiri-share-buttons-count' ).text( count );
				}
			} );
		}

		function twitter_button() {
			twitter.find( '.habakiri-share-buttons-button' ).click( function( e ) {
				e.preventDefault();
				window.open(
					$( this ).attr( 'href' ),
					'Share on Twitter',
					'width=550, height=400, menubar=no, toolbar=no, scrollbars=yes'
				);
			} );
		}

		function facebook_count() {
			var api = '//graph.facebook.com/?id=' + params.url;
			$.ajax( {
				url     : api,
				dataType: 'jsonp',
				success : function( json ) {
					console.log(json);
					var count = json.share.share_count ? json.share.share_count : 0;
					facebook.find( '.habakiri-share-buttons-count' ).text( count );
				}
			} );
		}

		function facebook_button() {
			facebook.find( '.habakiri-share-buttons-button' ).click( function( e ) {
				e.preventDefault();
				window.open(
					$( this ).attr( 'href' ),
					'Share on Facebook',
					'width=670, height=400, menubar=no, toolbar=no, scrollbars=yes'
				);
			} );
		}

		function hatena_count() {
			var api = ( location.protocol === 'https:' ? 'https://b.hatena.ne.jp' : 'http://api.b.st-hatena.com' ) + '/entry.count?url='  + params.url;
			$.ajax( {
				url     : api,
				dataType: 'jsonp',
				success : function( json ) {
					var count = json ? json : 0;
					hatena.find( '.habakiri-share-buttons-count' ).text( count );
				}
			} );
		}

		function hatena_button() {
			hatena.find( '.habakiri-share-buttons-button' ).click( function( e ) {
				e.preventDefault();
				window.open(
					$( this ).attr( 'href' ),
					'Hatena Bookmark',
					'width=510, height=420, menubar=no, toolbar=no, scrollbars=yes'
				);
			} );
		}

		function pocket_count() {
			$.ajax( {
				url     : habakiri_share_buttons_pocket.endpoint,
				dataType: 'json',
				data    : {
					action     : habakiri_share_buttons_pocket.action,
					_ajax_nonce: habakiri_share_buttons_pocket._ajax_nonce,
					post_id    : params.post_id
				},
				success : function( json ) {
					var count = json ? json : 0;
					pocket.find( '.habakiri-share-buttons-count' ).text( count );
				}
			} );
		}

		function pocket_button() {
			pocket.find( '.habakiri-share-buttons-button' ).click( function( e ) {
				e.preventDefault();
				window.open(
					$( this ).attr( 'href' ),
					'Pocket',
					'width=550, height=350, menubar=no, toolbar=no, scrollbars=yes'
				);
			} );
		}

		function feedly_count() {
			$.ajax( {
				url     : habakiri_share_buttons_feedly.endpoint,
				dataType: 'json',
				data    : {
					action     : habakiri_share_buttons_feedly.action,
					_ajax_nonce: habakiri_share_buttons_feedly._ajax_nonce
				},
				success : function( json ) {
					var count = json.subscribers ? json.subscribers : 0;
					feedly.find( '.habakiri-share-buttons-count' ).text( count );
				}
			} );
		}
	};

	$( '.habakiri-share-buttons' ).habakiri_share_buttons();
} );

},{}]},{},[1]);
