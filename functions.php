<?php
/**
 * Vivere theme setup.
 *
 * @package Vivere
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action(
	'after_setup_theme',
	function () {
		add_theme_support( 'wp-block-styles' );
		add_theme_support( 'editor-styles' );
		add_theme_support( 'custom-logo', array( 'flex-width' => true, 'flex-height' => true ) );
		add_editor_style( 'assets/css/vivere.css' );
	}
);

add_action(
	'wp_enqueue_scripts',
	function () {
		$ver = wp_get_theme()->get( 'Version' );
		wp_enqueue_style( 'vivere', get_theme_file_uri( 'assets/css/vivere.css' ), array(), $ver );
		wp_enqueue_script(
			'vivere',
			get_theme_file_uri( 'assets/js/vivere.js' ),
			array(),
			$ver,
			array( 'in_footer' => true, 'strategy' => 'defer' )
		);
		$args = array( 'in_footer' => true, 'strategy' => 'defer' );
		wp_enqueue_script( 'gsap', get_theme_file_uri( 'assets/js/vendor/gsap.min.js' ), array(), '3.13.0', $args );
		wp_enqueue_script( 'gsap-scrolltrigger', get_theme_file_uri( 'assets/js/vendor/ScrollTrigger.min.js' ), array( 'gsap' ), '3.13.0', $args );
		wp_enqueue_script( 'gsap-splittext', get_theme_file_uri( 'assets/js/vendor/SplitText.min.js' ), array( 'gsap' ), '3.13.0', $args );
		wp_enqueue_script( 'vivere-motion', get_theme_file_uri( 'assets/js/vivere-motion.js' ), array( 'gsap', 'gsap-scrolltrigger', 'gsap-splittext' ), $ver, $args );
		wp_enqueue_style( 'vivere-nav', get_theme_file_uri( 'assets/css/vivere-nav.css' ), array( 'vivere' ), $ver );
		wp_enqueue_script( 'vivere-nav', get_theme_file_uri( 'assets/js/vivere-nav.js' ), array(), $ver, $args );
		$logo = get_theme_mod( 'custom_logo' );
		wp_add_inline_script(
			'vivere-nav',
			'window.vvNav=' . wp_json_encode(
				array(
					'img'      => get_theme_file_uri( 'assets/images/' ),
					'logo'     => $logo ? wp_get_attachment_image_url( $logo, 'medium' ) : '',
					'login'    => is_user_logged_in() ? admin_url( 'profile.php' ) : wp_login_url(),
					'loggedIn' => is_user_logged_in(),
				)
			) . ';',
			'before'
		);
	}
);

/* Mark the document as JS-capable before paint, so the hero never flashes stacked slides. */
add_action(
	'wp_head',
	function () {
		wp_print_inline_script_tag( 'document.documentElement.classList.add("vv-js");if(!window.matchMedia("(prefers-reduced-motion: reduce)").matches){document.documentElement.classList.add("vv-motion");setTimeout(function(){if(!window.vvMotion){document.documentElement.classList.remove("vv-motion");}},4000);}' );
	},
	1
);

add_action(
	'init',
	function () {
		register_block_pattern_category( 'vivere', array( 'label' => __( 'Vivere', 'vivere' ) ) );

		register_block_style( 'core/button', array( 'name' => 'vv-light', 'label' => __( 'Light', 'vivere' ) ) );
		register_block_style( 'core/button', array( 'name' => 'vv-outline', 'label' => __( 'Outline', 'vivere' ) ) );
		register_block_style( 'core/button', array( 'name' => 'vv-outline-light', 'label' => __( 'Outline on dark', 'vivere' ) ) );
		register_block_style( 'core/group', array( 'name' => 'vv-card', 'label' => __( 'Card', 'vivere' ) ) );
	}
);

/**
 * Image URL helper for patterns.
 *
 * @param string $file File name inside assets/images.
 * @return string
 */
function vivere_img( $file ) {
	return esc_url( get_theme_file_uri( 'assets/images/' . $file ) );
}

/*
 * Newsletter sign-ups ("Join the Vivere World").
 * Stored as a private post type so the team can see and export them under Tools > Subscribers
 * until a mailing service is connected.
 */
add_action(
	'init',
	function () {
		register_post_type(
			'vv_subscriber',
			array(
				'label'           => __( 'Subscribers', 'vivere' ),
				'public'          => false,
				'show_ui'         => true,
				'show_in_menu'    => 'tools.php',
				'supports'        => array( 'title' ),
				'capability_type' => 'post',
				'capabilities'    => array( 'create_posts' => 'do_not_allow' ),
				'map_meta_cap'    => true,
			)
		);
	}
);

/**
 * Handle the join form.
 */
function vivere_handle_join() {
	$back = wp_get_referer() ? wp_get_referer() : home_url( '/' );
	$back = remove_query_arg( 'joined', $back );

	// Honeypot: bots fill every field.
	if ( ! empty( $_POST['vv_website'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		wp_safe_redirect( add_query_arg( 'joined', '1', $back ) . '#join' );
		exit;
	}

	$email = isset( $_POST['vv_email'] ) ? sanitize_email( wp_unslash( $_POST['vv_email'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
	$name  = isset( $_POST['vv_name'] ) ? sanitize_text_field( wp_unslash( $_POST['vv_name'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification

	if ( ! is_email( $email ) ) {
		wp_safe_redirect( add_query_arg( 'joined', 'invalid', $back ) . '#join' );
		exit;
	}

	$exists = get_posts(
		array(
			'post_type'   => 'vv_subscriber',
			'post_status' => 'any',
			'title'       => $email,
			'numberposts' => 1,
			'fields'      => 'ids',
		)
	);

	if ( ! $exists ) {
		$id = wp_insert_post(
			array(
				'post_type'   => 'vv_subscriber',
				'post_status' => 'private',
				'post_title'  => $email,
			)
		);
		if ( $id && $name ) {
			update_post_meta( $id, 'first_name', $name );
		}
	}

	wp_safe_redirect( add_query_arg( 'joined', '1', $back ) . '#join' );
	exit;
}
add_action( 'admin_post_nopriv_vivere_join', 'vivere_handle_join' );
add_action( 'admin_post_vivere_join', 'vivere_handle_join' );

/*
 * Enquiries from the contact, order, consultation and waitlist forms.
 * Saved under Tools > Enquiries and emailed to the site admin address.
 */
add_action(
	'init',
	function () {
		register_post_type(
			'vv_enquiry',
			array(
				'label'           => __( 'Enquiries', 'vivere' ),
				'public'          => false,
				'show_ui'         => true,
				'show_in_menu'    => 'tools.php',
				'supports'        => array( 'title', 'editor' ),
				'capability_type' => 'post',
				'capabilities'    => array( 'create_posts' => 'do_not_allow' ),
				'map_meta_cap'    => true,
			)
		);
	}
);

/**
 * Handle any Vivere enquiry form.
 */
function vivere_handle_contact() {
	$back = wp_get_referer() ? remove_query_arg( 'sent', wp_get_referer() ) : home_url( '/' );
	$back = preg_replace( '/#.*$/', '', $back );
	// phpcs:disable WordPress.Security.NonceVerification
	if ( ! empty( $_POST['vv_website'] ) ) {
		wp_safe_redirect( add_query_arg( 'sent', '1', $back ) );
		exit;
	}
	$email = isset( $_POST['vv_email'] ) ? sanitize_email( wp_unslash( $_POST['vv_email'] ) ) : '';
	if ( ! is_email( $email ) ) {
		wp_safe_redirect( add_query_arg( 'sent', 'invalid', $back ) );
		exit;
	}
	$topic = isset( $_POST['vv_topic'] ) ? sanitize_text_field( wp_unslash( $_POST['vv_topic'] ) ) : 'Enquiry';
	$name  = isset( $_POST['vv_name'] ) ? sanitize_text_field( wp_unslash( $_POST['vv_name'] ) ) : '';
	$lines = array();
	foreach ( $_POST as $k => $v ) {
		if ( 0 !== strpos( $k, 'vv_' ) || in_array( $k, array( 'vv_website', 'vv_topic' ), true ) || is_array( $v ) ) {
			continue;
		}
		$lines[] = ucfirst( substr( $k, 3 ) ) . ': ' . sanitize_textarea_field( wp_unslash( $v ) );
	}
	// phpcs:enable
	$body = implode( "\n", $lines );
	wp_insert_post(
		array(
			'post_type'    => 'vv_enquiry',
			'post_status'  => 'private',
			'post_title'   => $topic . ': ' . ( $name ? $name : $email ),
			'post_content' => $body,
		)
	);
	wp_mail( get_option( 'admin_email' ), '[Vivere] ' . $topic . ' from ' . ( $name ? $name : $email ), $body, array( 'Reply-To: ' . $email ) );
	$hash = '';
	if ( preg_match( '/#([\w-]+)$/', (string) wp_get_referer(), $mm ) ) {
		$hash = '#' . $mm[1];
	}
	wp_safe_redirect( add_query_arg( 'sent', '1', $back ) . $hash );
	exit;
}
add_action( 'admin_post_nopriv_vivere_contact', 'vivere_handle_contact' );
add_action( 'admin_post_vivere_contact', 'vivere_handle_contact' );
