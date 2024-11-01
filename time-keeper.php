<?php
/*
Plugin Name: Time Keeper
Plugin URI: http://webnist.jp
Version: 0.7.1.0
Description: Post time to measure
Author: Webnist and Understandard
Author URI: http://webnist.jp
Text Domain: time-keeper
Domain Path: /languages/
*/

if ( ! defined( 'TIME_KEEPER_DIR' ) )
	define( 'TIME_KEEPER_DIR', WP_PLUGIN_DIR . '/time-keeper' );

if ( ! defined( 'TIME_KEEPER_URL' ) )
	define( 'TIME_KEEPER_URL', WP_PLUGIN_URL . '/time-keeper' );

load_plugin_textdomain( 'time-keeper', false, '/time-keeper/languages' );

add_action( 'admin_print_scripts', 'add_time_keeper_js' );
function add_time_keeper_js() {
	wp_enqueue_script( 'jquery' );
	wp_enqueue_script( 'jquery-timers', TIME_KEEPER_URL . '/js/jquery.timers.js' );
}

add_action( 'admin_print_styles', 'add_admin_time_keeper_style' );
function add_admin_time_keeper_style() {
	wp_enqueue_style( 'time-keeper-admin-style', TIME_KEEPER_URL . '/css/admin_style.css' );
}

/* *** スライド用スクリプト *** */
add_action( 'admin_head', 'add_time_keeper_script' );
function add_time_keeper_script() { ?>
	<script type="text/javascript">
		jQuery(document).ready(function($){
			var i=0;
			$(document).everyTime(1000,'timer01',function(){
				i++;
				$('input#time_keeper').val(i);
			});
		});
	</script>
<?php }

add_action( 'admin_menu', 'time_keeper_set' );
function time_keeper_set() {
	add_meta_box( 'time_keeper', 'Time Keeper', 'time_keeper_custom_box', 'post', 'normal', 'core' );
	add_meta_box( 'time_keeper', 'Time Keeper', 'time_keeper_custom_box', 'page', 'normal', 'core' );
}

function time_keeper_custom_box() {
	$post_id = get_the_ID();
	$get_noncename = 'time_keeper_noncename';
	$get_key = '_time_keeper';
	$get_last_value = esc_attr( get_post_meta( $post_id, $get_key, true ) );
	echo '<input type="hidden" name="' . $get_noncename . '" id="' . $get_noncename . '" value="' . wp_create_nonce( plugin_basename(__FILE__) ) . '" />';
	echo '<input type="hidden" id="time_keeper" name="time_keeper" value="" />';
	if ( $get_last_value ) {
		echo '<input type="hidden" id="last_time_keeper" name="last_time_keeper" value="' . $get_last_value . '" />';
	}
}

add_action('save_post', 'time_keeper_save');
function time_keeper_save( $post_id ) {
	$get_noncename = 'time_keeper_noncename';
	$get_key = 'time_keeper';
	$get_last_key = 'last_time_keeper';
	$meta_key = '_time_keeper';
	$last_meta_key = '_last_time_keeper';
	$get_value = esc_attr( $_POST[$get_key] );
	$get_last_value = esc_attr( $_POST[$get_last_key] );
	if ( !wp_verify_nonce( $_POST[$get_noncename], plugin_basename(__FILE__) )) {
		return $post_id;
	}
	if ( !current_user_can( 'edit_page', $post_id )) {
		return $post_id;
	}
	if ( '' == get_post_meta( $post_id, $last_meta_key ) ) {
		add_post_meta( $post_id, $last_meta_key, $get_last_value, true );
	} else if ( $last_meta_key != get_post_meta( $post_id, $last_meta_key ) ) {
		update_post_meta( $post_id, $last_meta_key, $get_last_value );
	} else if ( '' == $last_meta_key ) {
		delete_post_meta( $post_id, $last_meta_key ) ;
	}
	if ( '' == get_post_meta( $post_id, $meta_key ) ) {
		add_post_meta( $post_id, $meta_key, $get_value, true );
	} else if ( $meta_key != get_post_meta( $post_id, $meta_key ) ) {
		update_post_meta( $post_id, $meta_key, $get_value );
	} else if ( '' == $meta_key ) {
		delete_post_meta( $post_id, $meta_key ) ;
	}
}

add_filter( 'manage_posts_columns', 'time_keeper_columns' );
add_filter( 'manage_pages_columns', 'time_keeper_columns' );
add_action( 'manage_posts_custom_column', 'add_time_keeper_column', 10, 2 );
add_action( 'manage_pages_custom_column', 'add_time_keeper_column', 10, 2 );

function time_keeper_columns( $posts_columns ) {
	$posts_columns['Time Keeper'] = __( 'Time Keeper', 'time-keeper' );
	return $posts_columns;
}

function add_time_keeper_column( $column_name, $post_id ) {
	if( $column_name == 'Time Keeper' ) {
		$get_key = '_time_keeper';
		$get_last_key = '_last_time_keeper';
		if ( $time = esc_attr( get_post_meta( $post_id, $get_key, true ) ) ) {
			$time = date( 'H:i:s', $time );
			$output = sprintf( __( 'Time: %s', 'time-keeper' ), $time );
		}
		if ( $last_time = esc_attr( get_post_meta( $post_id, $get_last_key, true ) ) ) { 
			$last_time = date( 'H:i:s', $last_time );
			$output .= '<br />' . "\n";
			$output .= sprintf( __( 'Last Time: %s', 'time-keeper' ), $last_time );
		}
	}
	if ( isset( $output ) && $output ) {
		echo $output;
	}
}
