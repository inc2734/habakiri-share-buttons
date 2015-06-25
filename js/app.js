(function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);var f=new Error("Cannot find module '"+o+"'");throw f.code="MODULE_NOT_FOUND",f}var l=n[o]={exports:{}};t[o][0].call(l.exports,function(e){var n=t[o][1][e];return s(n?n:e)},l,l.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(require,module,exports){
/**
 * Version    : 1.0.0
 * Author     : Takashi Kitajima
 * Author URI : http://2inc.org
 * Create     : June 15, 2015
 * Modified   :
 * License    : GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

jQuery( function( $ ) {
	$.fn.habakiri_share_buttons = function( params ) {
		var container = this;
		var params = $.extend( {
			title: container.data( 'habakiri-share-buttons-title' ),
			url  : container.data( 'habakiri-share-buttons-url' )
		}, params );

		params.title = encodeURIComponent( params.title );

		var facebook = container.find( '.habakiri-share-buttons-facebook' );
		var twitter  = container.find( '.habakiri-share-buttons-twitter' );
		var hatena   = container.find( '.habakiri-share-buttons-hatena' );

		return container.each( function() {
			facebook_count();
			facebook_button();
			twitter_count();
			twitter_button();
			hatena_count();
			hatena_button();
		} );

		function twitter_count() {
			var api = 'http://urls.api.twitter.com/1/urls/count.json?url=' + params.url;
			$.ajax( {
				url     : api,
				dataType: 'jsonp',
				success : function( json ) {
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
			var api = 'http://graph.facebook.com/?id=' + params.url;
			$.ajax( {
				url     : api,
				dataType: 'jsonp',
				success : function( json ) {
					var count = json.shares ? json.shares : 0;
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
			var api = 'http://api.b.st-hatena.com/entry.count?url=' + params.url;
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
	};

	$( '.habakiri-share-buttons' ).habakiri_share_buttons();
} );

},{}]},{},[1]);
