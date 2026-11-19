<?php
/**
 * One-off content builder: creates the Vivere inner pages from native blocks.
 * Not loaded by the theme. Run once from WP-CLI or an admin tool:  require this file, then vivere_build_pages().
 * Content comes from the old site's content store (option vivere_legacy_content) and media map (vivere_media_map).
 *
 * @package Vivere
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const VV_WA = 'https://wa.me/2347079606881';

/** House style: no em-dashes. Spaced dashes become commas. */
function vv_nodash( $s ) { return preg_replace( '/\s*\x{2014}\s*/u', ', ', $s ); }
function vv_e( $s ) { return esc_html( $s ); }
function vv_a( $s ) { return esc_attr( $s ); }
function vv_j( $a ) { return wp_json_encode( $a, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ); }

function vv_p( $text, $class = '' ) {
	$attr = $class ? ' ' . vv_j( array( 'className' => $class ) ) : '';
	$cls  = $class ? ' class="' . vv_a( $class ) . '"' : '';
	return "<!-- wp:paragraph{$attr} -->\n<p{$cls}>{$text}</p>\n<!-- /wp:paragraph -->\n\n";
}

function vv_h( $text, $level = 2, $class = '' ) {
	$at = array();
	if ( 2 !== $level ) { $at['level'] = $level; }
	if ( $class ) { $at['className'] = $class; }
	$attr = $at ? ' ' . vv_j( $at ) : '';
	$cls  = 'wp-block-heading' . ( $class ? ' ' . $class : '' );
	return "<!-- wp:heading{$attr} -->\n<h{$level} class=\"" . vv_a( $cls ) . "\">{$text}</h{$level}>\n<!-- /wp:heading -->\n\n";
}

function vv_group( $inner, $class = '', $tag = 'div', $extra = array() ) {
	$at = array_merge( $extra, array() );
	if ( 'div' !== $tag ) { $at['tagName'] = $tag; }
	if ( $class ) { $at['className'] = $class; }
	$layout = isset( $extra['layout'] ) ? $extra['layout'] : array( 'type' => 'default' );
	unset( $at['layout'] );
	$at['layout'] = $layout;
	$align  = isset( $at['align'] ) ? ' align' . $at['align'] : '';
	$anchor = isset( $at['anchor'] ) ? ' id="' . vv_a( $at['anchor'] ) . '"' : '';
	$order  = array();
	foreach ( array( 'tagName', 'align', 'anchor', 'className', 'layout' ) as $k ) {
		if ( isset( $at[ $k ] ) ) { $order[ $k ] = $at[ $k ]; }
	}
	$cls = trim( 'wp-block-group' . $align . ( $class ? ' ' . $class : '' ) );
	return '<!-- wp:group ' . vv_j( $order ) . " -->\n<{$tag}{$anchor} class=\"" . vv_a( $cls ) . "\">{$inner}</{$tag}>\n<!-- /wp:group -->\n\n";
}

function vv_section( $inner, $class, $anchor = '' ) {
	$extra = array( 'align' => 'full', 'layout' => array( 'type' => 'constrained' ) );
	if ( $anchor ) { $extra['anchor'] = $anchor; }
	return vv_group( $inner, 'vv-sec ' . $class, 'section', $extra );
}

function vv_img( $id, $alt = '', $class = '' ) {
	$url = wp_get_attachment_url( $id );
	if ( ! $url ) { return ''; }
	$at = array( 'id' => (int) $id, 'sizeSlug' => 'full', 'linkDestination' => 'none' );
	if ( $class ) { $at['className'] = $class; }
	$cls = 'wp-block-image size-full' . ( $class ? ' ' . $class : '' );
	return '<!-- wp:image ' . vv_j( $at ) . " -->\n<figure class=\"" . vv_a( $cls ) . '"><img src="' . esc_url( $url ) . '" alt="' . vv_a( $alt ) . '" class="wp-image-' . (int) $id . "\"/></figure>\n<!-- /wp:image -->\n\n";
}

function vv_theme_img( $file, $alt = '', $class = '' ) {
	$at  = array( 'sizeSlug' => 'full', 'linkDestination' => 'none' );
	if ( $class ) { $at['className'] = $class; }
	$cls = 'wp-block-image size-full' . ( $class ? ' ' . $class : '' );
	return '<!-- wp:image ' . vv_j( $at ) . " -->\n<figure class=\"" . vv_a( $cls ) . '"><img src="' . esc_url( get_theme_file_uri( 'assets/images/' . $file ) ) . '" alt="' . vv_a( $alt ) . "\"/></figure>\n<!-- /wp:image -->\n\n";
}

/** Buttons: array of [label, url, style] where style is '', 'vv-light', 'vv-outline', 'vv-outline-light'. */
function vv_buttons( $items, $class = '' ) {
	$out = '';
	foreach ( $items as $b ) {
		$style = isset( $b[2] ) && $b[2] ? 'is-style-' . $b[2] : '';
		$attr  = $style ? ' ' . vv_j( array( 'className' => $style ) ) : '';
		$out  .= "<!-- wp:button{$attr} -->\n<div class=\"wp-block-button" . ( $style ? ' ' . $style : '' ) . '"><a class="wp-block-button__link wp-element-button" href="' . esc_url( $b[1] ) . '">' . vv_e( $b[0] ) . "</a></div>\n<!-- /wp:button -->\n\n";
	}
	$attr = $class ? ' ' . vv_j( array( 'className' => $class ) ) : '';
	return "<!-- wp:buttons{$attr} -->\n<div class=\"wp-block-buttons" . ( $class ? ' ' . $class : '' ) . "\">" . rtrim( $out ) . "</div>\n<!-- /wp:buttons -->\n\n";
}

function vv_list( $items, $class = '', $ordered = false ) {
	$out = '';
	foreach ( $items as $i ) {
		$out .= "<!-- wp:list-item -->\n<li>{$i}</li>\n<!-- /wp:list-item -->\n\n";
	}
	$at = array();
	if ( $ordered ) { $at['ordered'] = true; }
	if ( $class ) { $at['className'] = $class; }
	$attr = $at ? ' ' . vv_j( $at ) : '';
	$tag  = $ordered ? 'ol' : 'ul';
	return "<!-- wp:list{$attr} -->\n<{$tag} class=\"wp-block-list" . ( $class ? ' ' . $class : '' ) . "\">" . rtrim( $out ) . "</{$tag}>\n<!-- /wp:list -->\n\n";
}

function vv_quote( $text, $cite, $class = 'vv-quote' ) {
	return '<!-- wp:quote ' . vv_j( array( 'className' => $class ) ) . " -->\n<blockquote class=\"wp-block-quote {$class}\">" . vv_p( $text ) . '<cite>' . $cite . "</cite></blockquote>\n<!-- /wp:quote -->\n\n";
}

function vv_details( $q, $a ) {
	return "<!-- wp:details -->\n<details class=\"wp-block-details\"><summary>" . vv_e( $q ) . '</summary>' . vv_p( vv_e( $a ) ) . "</details>\n<!-- /wp:details -->\n\n";
}

function vv_html( $html ) {
	return "<!-- wp:html -->\n{$html}\n<!-- /wp:html -->\n\n";
}

function vv_table( $head, $rows, $class = 'vv-table' ) {
	$th = '';
	foreach ( $head as $h ) { $th .= '<th>' . vv_e( $h ) . '</th>'; }
	$tb = '';
	foreach ( $rows as $r ) {
		$tb .= '<tr>';
		foreach ( $r as $c ) { $tb .= '<td>' . vv_e( $c ) . '</td>'; }
		$tb .= '</tr>';
	}
	return '<!-- wp:table ' . vv_j( array( 'hasFixedLayout' => false, 'className' => $class ) ) . " -->\n<figure class=\"wp-block-table {$class}\"><table><thead><tr>{$th}</tr></thead><tbody>{$tb}</tbody></table></figure>\n<!-- /wp:table -->\n\n";
}

/** Page header: kicker, H1, lead, optional banner image (media id). */
function vv_page_head( $kicker, $title, $lead, $banner = 0, $alt = '' ) {
	$left  = vv_group( vv_p( vv_e( $kicker ), 'vv-kicker' ) . vv_h( vv_e( $title ), 1, 'vv-page-h1' ), '' );
	$inner = vv_group( $left . vv_p( vv_e( $lead ), 'vv-lead' ), 'vv-page-head-in' );
	if ( $banner ) {
		$inner .= vv_img( $banner, $alt, 'vv-banner' );
	}
	return vv_section( $inner, 'vv-page-head' );
}

/** Media + text row. */
function vv_feature( $img_html, $body, $rev = false, $anchor = '' ) {
	$inner = vv_group( $img_html . vv_group( $body, 'vv-feature-body' ), 'vv-feature' . ( $rev ? ' vv-feature--rev' : '' ) );
	return vv_section( $inner, 'vv-sec--tight', $anchor );
}

function vv_band( $title, $text, $buttons ) {
	$inner = vv_group( vv_group( vv_h( vv_e( $title ) ) . vv_p( vv_e( $text ) ), '' ) . vv_buttons( $buttons ), 'vv-band' );
	return vv_section( $inner, 'vv-sec--band' );
}

/** A form posting to admin-post. $fields: array of [type, name, label, options|placeholder, required]. */
function vv_form( $topic, $fields, $submit, $dark = false ) {
	$h  = '<form class="vv-form' . ( $dark ? ' vv-form--dark' : '' ) . '" method="post" action="/wp-admin/admin-post.php">' . "\n";
	$h .= "\t" . '<input type="hidden" name="action" value="vivere_contact">' . "\n";
	$h .= "\t" . '<input type="hidden" name="vv_topic" value="' . vv_a( $topic ) . '">' . "\n";
	foreach ( $fields as $f ) {
		list( $type, $name, $label ) = $f;
		$id   = 'vv-' . sanitize_title( $topic ) . '-' . $name;
		$req  = ! empty( $f[4] ) ? ' required' : '';
		$wide = in_array( $type, array( 'textarea', 'select' ), true ) || ! empty( $f[5] ) ? ' vv-field--wide' : '';
		$h   .= "\t<p class=\"vv-field{$wide}\"><label for=\"{$id}\">" . vv_e( $label ) . '</label>';
		if ( 'textarea' === $type ) {
			$h .= "<textarea id=\"{$id}\" name=\"vv_{$name}\" rows=\"5\"{$req}></textarea>";
		} elseif ( 'select' === $type ) {
			$h .= "<select id=\"{$id}\" name=\"vv_{$name}\"{$req}>";
			foreach ( $f[3] as $o ) { $h .= '<option>' . vv_e( $o ) . '</option>'; }
			$h .= '</select>';
		} else {
			$ac = array( 'email' => 'email', 'tel' => 'tel' );
			$autocomplete = 'name' === $name ? ' autocomplete="name"' : ( isset( $ac[ $type ] ) ? ' autocomplete="' . $ac[ $type ] . '"' : '' );
			$ph = isset( $f[3] ) && is_string( $f[3] ) && $f[3] ? ' placeholder="' . vv_a( $f[3] ) . '"' : '';
			$h .= "<input id=\"{$id}\" name=\"vv_{$name}\" type=\"{$type}\"{$autocomplete}{$ph}{$req}>";
		}
		$h .= "</p>\n";
	}
	$h .= "\t" . '<input class="vv-hp" type="text" name="vv_website" tabindex="-1" autocomplete="off" aria-hidden="true">' . "\n";
	$h .= "\t" . '<p class="vv-field vv-field--wide vv-form-foot"><button type="submit" class="vv-submit">' . vv_e( $submit ) . '</button><span class="vv-form-msg" role="status"></span></p>' . "\n</form>";
	return vv_html( $h );
}

function vv_contact_fields( $extra = array() ) {
	return array_merge(
		array(
			array( 'text', 'name', 'Full name', '', true ),
			array( 'email', 'email', 'Email', 'you@example.com', true ),
			array( 'tel', 'phone', 'Phone or WhatsApp', '+234', false, true ),
		),
		$extra,
		array( array( 'textarea', 'message', 'Message', '', false ) )
	);
}

function vv_naira( $n ) {
	return is_numeric( $n ) ? '₦' . number_format( (float) $n ) : $n;
}

function vv_upsert_page( $slug, $title, $content, $parent = 0 ) {
	$ex = get_page_by_path( $slug );
	$id = wp_insert_post(
		array(
			'ID'           => $ex ? $ex->ID : 0,
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_title'   => $title,
			'post_name'    => $slug,
			'post_content' => vv_nodash( $content ),
			'post_parent'  => $parent,
		)
	);
	if ( $id && ! is_wp_error( $id ) ) {
		update_post_meta( $id, '_wp_page_template', 'page-full' );
	}
	return $id;
}

/* ------------------------------------------------------------ v2 helpers */

/** A highlighted phrase. The motion script sweeps the highlight in when it scrolls into view. */
function vv_mark( $t ) {
	return '<mark class="vv-hl">' . vv_e( $t ) . '</mark>';
}

/** Stats row: array of [number, label]. Whole numbers count up on scroll. */
function vv_stats( $items, $class = '' ) {
	$out = '';
	foreach ( $items as $s ) {
		$out .= vv_group( vv_p( vv_e( $s[0] ), 'vv-stat-num' ) . vv_p( vv_e( $s[1] ), 'vv-stat-label' ), 'vv-stat' );
	}
	return vv_group( $out, trim( 'vv-stats ' . $class ) );
}

/**
 * Full-bleed page hero, edited like any other group.
 * $o: tone (night|plum|ink|wine), tag, title (HTML), lead, buttons, media (image block HTML, first is the large one), stats, word, short.
 */
function vv_hero( $o ) {
	$copy  = vv_h( $o['title'], 1, 'vv-phero-title' );
	$copy .= vv_p( vv_e( $o['lead'] ), 'vv-phero-lead' );
	if ( ! empty( $o['buttons'] ) ) {
		$copy .= vv_buttons( $o['buttons'], 'vv-phero-ctas' );
	}
	$media = implode( '', $o['media'] );
	if ( ! empty( $o['stats'] ) ) {
		$media .= vv_stats( $o['stats'], 'vv-phero-chip' );
	}
	$inner = vv_group( vv_group( $copy, 'vv-phero-copy' ) . vv_group( $media, 'vv-phero-media vv-phero-media--' . count( $o['media'] ) ), 'vv-phero-in' );
	if ( ! empty( $o['word'] ) ) {
		$inner .= vv_p( vv_e( $o['word'] ), 'vv-phero-word' );
	}
	$cls = 'vv-phero vv-phero--' . $o['tone'] . ( ! empty( $o['short'] ) ? ' vv-phero--short' : '' );
	return vv_group( $inner, $cls, 'section', array( 'align' => 'full', 'layout' => array( 'type' => 'default' ) ) );
}

/** Scrolling ribbon of words. */
function vv_marquee( $items, $class = '' ) {
	$out = '';
	foreach ( $items as $i ) {
		$out .= vv_p( vv_e( $i ), 'vv-marquee-item' );
	}
	return vv_group( vv_group( $out, 'vv-marquee-track' ), trim( 'vv-marquee ' . $class ), 'div', array( 'align' => 'full' ) );
}

/** A card in a stack that pins and layers as you scroll. Tone: white|plum|sand|night. */
function vv_stack_card( $img, $body, $tone, $anchor = '' ) {
	$extra = $anchor ? array( 'anchor' => $anchor ) : array();
	return vv_group( $img . vv_group( $body, 'vv-stack-body' ), 'vv-stack-card vv-tone-' . $tone, 'div', $extra );
}

/** Numbered steps joined by a line that draws itself. $steps: array of [title, text]. */
function vv_flow( $steps ) {
	$out = '';
	foreach ( $steps as $n => $s ) {
		$out .= vv_group( vv_p( sprintf( '%02d', $n + 1 ), 'vv-flow-num' ) . vv_h( vv_e( $s[0] ), 3 ) . vv_p( vv_e( $s[1] ) ), 'vv-flow-step' );
	}
	return vv_group( $out, 'vv-flow' );
}

function vv_chips( $items ) {
	$out = '';
	foreach ( $items as $i ) {
		$out .= vv_p( vv_e( $i ), 'vv-chip' );
	}
	return vv_group( $out, 'vv-chips' );
}

/** Closing call to action. */
function vv_band2( $title_html, $text, $buttons ) {
	$inner = vv_group( vv_h( $title_html, 2, 'vv-band2-title' ) . vv_p( vv_e( $text ), 'vv-band2-text' ), 'vv-band2-copy' ) . vv_buttons( $buttons, 'vv-band2-ctas' );
	return vv_section( vv_group( $inner, 'vv-band2' ), 'vv-sec--band' );
}

function vv_order_section( $title_html, $lead, $form, $anchor, $buttons = array() ) {
	$left = vv_h( $title_html, 2, 'vv-sec-title' ) . vv_p( vv_e( $lead ), 'vv-lead' );
	if ( $buttons ) { $left .= vv_buttons( $buttons ); }
	return vv_section( vv_group( vv_group( $left, 'vv-order-copy' ) . $form, 'vv-split vv-form-wrap' ), 'vv-order', $anchor );
}

/* ------------------------------------------------------------------ pages */

function vv_page_about( $m, $j ) {
	$c = vv_hero(
		array(
			'tone'    => 'night',
			'tag'     => 'The Vivere story',
			'title'   => 'Built from the ' . vv_mark( 'inside out.' ),
			'lead'    => 'The quality of a life is determined not by circumstance, but by the quality of the person you have become.',
			'buttons' => array( array( 'Meet Ann Uramu', '#ann', 'vv-light' ), array( 'Explore Vivere', '/frames/', 'vv-outline-light' ) ),
			'media'   => array(
				vv_theme_img( 'hero-1.jpg', 'A woman smiling in warm light', 'vv-phero-img vv-phero-img--a' ),
				vv_theme_img( 'join.jpg', 'Friends talking around a table', 'vv-phero-img vv-phero-img--b' ),
				vv_img( $m['home-beginHere-frames'], 'A framed portrait on a wall', 'vv-phero-img vv-phero-img--c' ),
			),
			'stats'   => array( array( '10+', 'years building people and organisations' ) ),
			'word'    => 'Vivere',
		)
	);

	$words = vv_group(
		vv_p( 'To exist', 'vv-word vv-word--no' ) . vv_p( 'To endure', 'vv-word vv-word--no' ) . vv_p( 'To live', 'vv-word vv-word--yes' ),
		'vv-words'
	);
	$c .= vv_section(
		vv_p( 'Vivere is a Latin word. It means to live: not merely to exist, not simply to endure, but to inhabit life fully, intentionally and with purpose.', 'vv-statement' ) . $words,
		'vv-about-word'
	);
	$c .= vv_section(
		vv_group(
			vv_group( vv_h( 'A word. A worldview. ' . vv_mark( 'A way of living.' ), 2, 'vv-sec-title' ), '' ) .
			vv_group(
				vv_p( 'That single word is the heartbeat of everything this brand is and everything it does.', 'vv-lead' ) .
				vv_p( 'Vivere Global was founded on a conviction that has been proven true over and over again: that the quality of a person\'s life is determined not by their circumstances, but by the quality of the person they have become. Excellence is not inherited. It is built, framework by framework, decision by decision, investment by investment.' ) .
				vv_p( 'We exist for the person ready to do that inner work. Who is done with the mediocre version of themselves and ready to architect something intentional and exceptional. Whatever stage of your personal transformation journey you are on, Vivere Global is your world.' ),
				'vv-prose'
			),
			'vv-split'
		),
		'vv-sec--tight'
	);

	$body  = vv_p( 'Founder · Author · Keynote speaker', 'vv-kicker' );
	$body .= vv_h( 'Meet ' . vv_mark( 'Ann Uramu.' ), 2, 'vv-sec-title' );
	$body .= vv_p( 'For years, the words people use to describe Ann Uramu have been remarkably consistent: the brilliance of her mind, the excellence of her personality, her poise and carriage. What most people do not know is that none of it happened by chance.' );
	$body .= vv_p( 'Ann is a personal transformation expert, author and the founder of Vivere Global. With a background in Psychology and Human Capital Development, and over a decade of experience building organisations, developing people and designing systems for growth, she has spent her life doing one thing with extraordinary consistency: becoming, intentionally and rigorously.' );
	$body .= vv_chips( array( 'Psychology', 'Human Capital Development', 'Author', 'Keynote speaker' ) );
	$body .= vv_p( 'Vivere Global is the formalisation of that life\'s work. The frameworks she developed for herself. The disciplines she maintained. The values she refused to compromise. All of it, now available to you.' );
	$body .= vv_quote( '“All these qualities people talk about did not happen by accident. They were intentionally engineered. Today I bring you the tools that shaped me, so that you can begin the journey to becoming the highest version of yourself.”', 'Ann Uramu', 'vv-quote vv-quote--pop' );
	$c    .= vv_feature( vv_img( $m['home-comingSoon-regal-essence'], 'Ann Uramu', 'vv-feature-img' ), $body, false, 'ann' );

	$cards = '';
	foreach ( $j['testimonials'] as $t ) {
		if ( empty( $t['show'] ) ) { continue; }
		$cards .= vv_quote( '“' . vv_e( $t['quote'] ) . '”', vv_e( $t['name'] . ', ' . $t['descriptor'] ), 'vv-quote vv-testimonial' );
	}
	$c .= vv_section(
		vv_group( vv_h( 'Voices from the ' . vv_mark( 'Vivere world.' ), 2, 'vv-sec-title' ) . vv_p( 'Hover to pause.', 'vv-hint' ), 'vv-head' ),
		'vv-testimonials vv-testimonials--head'
	);
	$c .= vv_group( vv_group( $cards, 'vv-tmarquee-track' ), 'vv-tmarquee', 'div', array( 'align' => 'full' ) );
	$c .= vv_band2( 'Your world is ' . vv_mark( 'waiting.' ), 'The frames on your walls. The books on your shelf. The programs on the horizon. Everything Vivere builds is an invitation to become.', array( array( 'Explore Frames', '/frames/', 'vv-light' ), array( 'Vivere Reads', '/reads/', 'vv-outline-light' ) ) );
	return $c;
}

function vv_page_frames( $m, $j ) {
	$p      = $j['framePricing'];
	$active = array_filter( $j['frames'], function ( $f ) { return ! empty( $f['active'] ); } );
	$c      = vv_hero(
		array(
			'tone'    => 'plum',
			'tag'     => 'Vivere Frames',
			'title'   => 'Inspiration that lives on your ' . vv_mark( 'walls.' ),
			'lead'    => 'Not décor. Intentional installations of inspiration, chosen to elevate your space and, in doing so, elevate you.',
			'buttons' => array( array( 'Order a frame', '#order', 'vv-light' ), array( 'See the collections', '#collections', 'vv-outline-light' ) ),
			'media'   => array(
				vv_img( $m['frame-aurum'], 'A gold portrait frame on a sunlit table', 'vv-phero-img vv-phero-img--a' ),
				vv_img( $m['frame-aurora'], 'A framed quote on a shelf', 'vv-phero-img vv-phero-img--b' ),
				vv_img( $m['frame-tabletop'], 'Two table top portrait frames', 'vv-phero-img vv-phero-img--c' ),
			),
			'stats'   => array( array( (string) count( $p['sizes'] ), 'sizes, all made to order' ), array( '5–7', 'working days to make' ) ),
			'word'    => 'Frames',
		)
	);
	$c .= vv_marquee( array( 'Aurora', 'Aurum', 'Anima', 'Table Top', 'Made to order' ), 'vv-marquee--plum' );
	$c .= vv_section(
		vv_group(
			vv_group( vv_h( 'Your environment speaks to you, ' . vv_mark( 'constantly and quietly.' ), 2, 'vv-sec-title' ), '' ) .
			vv_group(
				vv_p( 'The words that greet you when you wake up. The faces that remind you what is possible. The vision of your future that hangs where you cannot ignore it.', 'vv-lead' ) .
				vv_p( 'Some Vivere frames carry words that stir something in you. Some carry faces that remind you of who you can become. Some carry the vision of the life you are building. Each collection is named from Latin, rooted in meaning, and made to order.' ),
				'vv-prose'
			),
			'vv-split'
		),
		'vv-sec--tight'
	);

	$copy  = array(
		'aurora'   => array( 'Aurora · Latin for dawn', 'The Aurora Collection', array( 'Dawn is the moment everything shifts: the quiet arrival of light, the signal that something new is possible.', 'The Aurora Collection is built around words. Quotes, affirmations and declarations that greet you each day with the reminder of who you are becoming. Place one where you begin: your bedroom, your desk, your mirror.' ), 'Words that greet you. Words that shape you.' ),
		'aurum'    => array( 'Aurum · Latin for gold', 'The Aurum Series', array( 'Gold has always been the standard against which all other things are measured.', 'The Aurum Series is a collection of portrait frames, beginning with you. Because you are worth celebrating and worth placing on a wall as a daily reminder of the person you are becoming. Around you, the figures who shaped history and showed what is possible.' ), 'The faces of excellence. Beginning with yours.' ),
		'anima'    => array( 'Anima · Latin for soul', 'The Anima Range', array( 'Anima is the soul, the truest part of a person and the place where your most honest vision of your life lives.', 'The Anima Range is a collection of vision board frames. Words that resonate, images that describe your season and declarations of your future, held in a frame worthy of what they represent.' ), 'Your vision, framed. Your future, declared.' ),
		'tabletop' => array( 'Table top · 5 × 7 inches', 'Table Top Frames', array( 'Not every intentional space is a wall. The desk you sit at, the shelf you pass every morning, the bedside where the day begins and ends.', 'A single, refined 5 × 7 inch size for the words, portrait or vision you want within arm\'s reach. Every piece made to order.' ), 'Intentional inspiration, within arm\'s reach.' ),
	);
	$tones = array( 'white', 'plum', 'sand', 'night' );
	$cards = '';
	$i     = 0;
	foreach ( $active as $f ) {
		if ( ! isset( $copy[ $f['id'] ] ) ) { continue; }
		$x     = $copy[ $f['id'] ];
		$tone  = $tones[ $i % 4 ];
		$dark  = in_array( $tone, array( 'plum', 'night' ), true );
		$body  = vv_p( sprintf( '%02d', $i + 1 ) . ' / ' . sprintf( '%02d', count( $active ) ), 'vv-stack-count' );
		$body .= vv_p( vv_e( $x[0] ), 'vv-kicker' ) . vv_h( vv_e( $x[1] ), 2, 'vv-sec-title' );
		foreach ( $x[2] as $para ) { $body .= vv_p( vv_e( $para ) ); }
		$body .= vv_p( vv_e( $x[3] ), 'vv-note' );
		$price = is_numeric( $f['price'] ) ? 'From ' . vv_naira( $f['price'] ) : $f['price'];
		$body .= vv_p( vv_e( $price ) . ' · Made to order', 'vv-price' );
		$body .= vv_buttons( array( array( 'Order this frame', '#order', $dark ? 'vv-light' : '' ), array( 'Ask on WhatsApp', VV_WA, $dark ? 'vv-outline-light' : 'vv-outline' ) ) );
		$cards .= vv_stack_card( vv_img( $m[ 'frame-' . $f['id'] ], $x[1], 'vv-stack-img' ), $body, $tone, $f['id'] );
		$i++;
	}
	$c .= vv_section( vv_group( vv_h( 'Four ways to frame ' . vv_mark( 'your becoming.' ), 2, 'vv-sec-title' ) . vv_p( 'Scroll through the collections. Every piece is made for you.', 'vv-lead' ), 'vv-head' ) . vv_group( $cards, 'vv-stack' ), 'vv-stack-sec', 'collections' );

	$rows = array();
	foreach ( $p['sizes'] as $s ) { $rows[] = array( $s['label'], vv_naira( $s['price'] ) ); }
	$side  = vv_h( 'Sizes and ' . vv_mark( 'frame prices.' ), 2, 'vv-sec-title' );
	$side .= vv_p( 'Frame prices by size, added to the collection price. Glass front: ' . vv_naira( $p['glassAddOnPrice'] ) . ' extra. Table top frames come in glass (' . vv_naira( $p['tabletopMaterials'][0]['price'] ) . ') or wood (' . vv_naira( $p['tabletopMaterials'][1]['price'] ) . ').', 'vv-lead' );
	$side .= vv_h( 'How ordering works', 3 );
	$side .= vv_list( array( 'Choose your collection and size.', 'Send us your words, portrait or vision board images.', 'We confirm the details and send a secure payment link.', 'Production takes 5 to 7 working days.', 'Delivery takes 3 to 4 business days in Nigeria and 7 to 15 internationally.' ), 'vv-steps', true );
	$c    .= vv_section( vv_group( vv_group( $side, 'vv-prose' ) . vv_group( vv_table( array( 'Size', 'Price' ), $rows ), '' ), 'vv-split' ), 'vv-pricing' );

	$sizes = array_merge( array( 'Not sure yet' ), wp_list_pluck( $p['sizes'], 'label' ) );
	$form  = vv_form( 'Frame order', vv_contact_fields( array( array( 'select', 'collection', 'Collection', array( 'The Aurora Collection', 'The Aurum Series', 'The Anima Range', 'Table Top Frame (5 × 7 in)', 'Several frames or a custom size' ) ), array( 'select', 'size', 'Size', $sizes ) ) ), 'Request my frame' );
	$c    .= vv_order_section( 'Request ' . vv_mark( 'your frame.' ), 'Tell us what you would like and our concierge team will confirm the details, the price and delivery within 48 hours. Ordering several pieces? Message us on WhatsApp and we will handle it for you.', $form, 'order', array( array( 'Message concierge on WhatsApp', VV_WA, 'vv-outline' ) ) );

	$faq = '';
	foreach ( $j['faq'] as $q ) {
		if ( ! empty( $q['show'] ) ) { $faq .= vv_details( $q['question'], $q['answer'] ); }
	}
	$c .= vv_section( vv_group( vv_group( vv_h( 'Frames, ' . vv_mark( 'answered.' ), 2, 'vv-sec-title' ) . vv_p( 'Still unsure? The concierge team is one message away.', 'vv-lead' ) . vv_buttons( array( array( 'Ask on WhatsApp', VV_WA, 'vv-outline' ) ) ), 'vv-sticky' ) . vv_group( $faq, 'vv-faq' ), 'vv-split' ), 'vv-faq-sec' );
	$c .= vv_band2( 'Revamp a ' . vv_mark( 'whole space.' ), 'Office, home or workspace: we curate frames for the room you are building your vision in.', array( array( 'Vivere Spaces', '/spaces/', 'vv-light' ) ) );
	return $c;
}

function vv_page_reads( $m, $j ) {
	$cards  = '';
	$titles = array();
	$first  = 0;
	foreach ( $j['books'] as $b ) {
		if ( empty( $b['active'] ) || ( isset( $b['published'] ) && ! $b['published'] ) ) { continue; }
		$titles[] = $b['title'];
		$has      = isset( $m[ 'book-' . $b['id'] ] );
		if ( $has && ! $first ) { $first = $m[ 'book-' . $b['id'] ]; }
		$cards .= vv_group(
			( $has ? vv_img( $m[ 'book-' . $b['id'] ], $b['title'] . ' by ' . $b['author'], 'vv-book-cover' ) : '' ) .
			vv_h( vv_e( $b['title'] ), 3 ) .
			vv_p( vv_e( $b['author'] ), 'vv-book-author' ) .
			vv_p( vv_e( $b['shortSummary'] ) ) .
			vv_group( vv_p( vv_e( vv_naira( $b['price'] ) ), 'vv-price' ) . vv_buttons( array( array( 'Order', '#order', 'vv-outline' ) ) ), 'vv-book-foot' ),
			'vv-book'
		);
	}
	$c  = vv_hero(
		array(
			'tone'    => 'ink',
			'tag'     => 'Vivere Reads',
			'title'   => 'Cultivate your mind. Invest in your ' . vv_mark( 'becoming.' ),
			'lead'    => 'Books that challenge the way you think, expand the way you see, and shape the person you are becoming.',
			'buttons' => array( array( 'Browse the shelf', '#shelf', 'vv-light' ), array( 'Order a book', '#order', 'vv-outline-light' ) ),
			'media'   => array(
				vv_theme_img( 'reads.jpg', 'Hands holding an open book', 'vv-phero-img vv-phero-img--a' ),
				vv_img( $m['home-beginHere-reads'], 'A man reading outdoors', 'vv-phero-img vv-phero-img--b' ),
				vv_img( $first, 'A book from the Vivere shelf', 'vv-phero-img vv-phero-img--c' ),
			),
			'stats'   => array( array( (string) count( $titles ), 'books, each one personally read' ) ),
			'word'    => 'Reads',
		)
	);
	$c .= vv_section(
		vv_p( '“Every investment you make in yourself will announce itself, every time you speak, every time you walk into a room. Begin with a book.”', 'vv-statement' ) .
		vv_group( vv_p( 'Ann Uramu', 'vv-statement-cite' ) . vv_p( 'Every book in Vivere Reads has been personally read and recommended by Ann Uramu, not because it is popular but because it is powerful. These are the books that make you put them down, stare at the ceiling and think differently about your life.', 'vv-lead' ), 'vv-statement-foot' ),
		'vv-about-word'
	);
	$c .= vv_section( vv_group( vv_h( 'The ' . vv_mark( 'shelf.' ), 2, 'vv-sec-title' ) . vv_p( count( $titles ) . ' books, chosen with one question in mind: will this make someone more?', 'vv-lead' ), 'vv-head' ) . vv_group( $cards, 'vv-books' ), 'vv-shelf', 'shelf' );
	$form = vv_form( 'Book order', vv_contact_fields( array( array( 'select', 'book', 'Book', array_merge( $titles, array( 'A book that is not on the shelf' ) ) ) ) ), 'Send my order' );
	$c   .= vv_order_section( 'Looking for ' . vv_mark( 'a book?' ), 'Choose a title to order it, or tell us about one that is not on the shelf. We will check our library and get back to you within 48 to 72 hours with availability and a secure payment link.', $form, 'order', array( array( 'Order on WhatsApp', VV_WA, 'vv-outline' ) ) );
	$c   .= vv_band2( 'Take the ' . vv_mark( 'next step.' ), 'A word on the wall. A vision in the room. A program on the horizon.', array( array( 'Explore Frames', '/frames/', 'vv-light' ), array( 'See what is coming', '/experiences/', 'vv-outline-light' ) ) );
	return $c;
}

function vv_page_spaces( $m, $j ) {
	$c = vv_hero(
		array(
			'tone'    => 'wine',
			'tag'     => 'Vivere Spaces',
			'title'   => 'Rooms made with ' . vv_mark( 'intention.' ),
			'lead'    => 'Every great vision deserves an environment that reflects it.',
			'buttons' => array( array( 'Book a consultation', '#consultation', 'vv-light' ), array( 'Explore Frames', '/frames/', 'vv-outline-light' ) ),
			'media'   => array(
				vv_theme_img( 'hero-4.jpg', 'A bright, open living room', 'vv-phero-img vv-phero-img--a' ),
				vv_img( $m['home-beginHere-revamp'], 'A gallery wall of framed photographs', 'vv-phero-img vv-phero-img--b' ),
				vv_img( $m['revamp'], 'Three framed prints above a bed', 'vv-phero-img vv-phero-img--c' ),
			),
			'stats'   => array( array( '3', 'steps from first call to a curated room' ), array( '48h', 'to reply and set your call' ) ),
			'word'    => 'Spaces',
		)
	);
	$c .= vv_marquee( array( 'Homes', 'Offices', 'Workspaces', 'Studios', 'Executive gifting' ), 'vv-marquee--sand' );

	$body  = vv_h( 'Curate an environment worthy of the ' . vv_mark( 'vision you are building.' ), 2, 'vv-sec-title' );
	$body .= vv_p( 'Whether it is your office, your home or your workspace, we curate the space with soul-stirring frames that inspire excellence, elevate your perspective and move you towards the life and vision you are creating.' );
	$body .= vv_p( 'Corporate orders, executive gifting and workspace revamps are welcome.', 'vv-note' );
	$body .= vv_buttons( array( array( 'Book a consultation', '#consultation', '' ) ) );
	$c    .= vv_feature( vv_img( $m['home-beginHere-revamp'], 'A gallery wall of framed photographs in a hallway', 'vv-feature-img' ), $body );

	$c .= vv_section(
		vv_group( vv_h( 'What the ' . vv_mark( 'consultation' ) . ' includes.', 2, 'vv-sec-title' ), 'vv-head' ) .
		vv_flow(
			array(
				array( 'A dedicated call', 'We start by understanding your environment and your vision.' ),
				array( 'A proposed curation', 'A frame curation designed for the space and the person in it.' ),
				array( 'A full quote', 'With delivery, installation and turnaround options, so you can decide with clarity.' ),
			)
		),
		'vv-flow-sec'
	);

	$form = vv_form( 'Space consultation', vv_contact_fields( array( array( 'select', 'space', 'Type of space', array( 'Home', 'Office', 'Workspace or studio', 'Corporate or multiple spaces' ) ), array( 'text', 'location', 'City and country', '', false, true ) ) ), 'Request a consultation' );
	$c   .= vv_order_section( 'Schedule a ' . vv_mark( 'consultation call.' ), 'Tell us a little about the space. We will reply within 48 hours to set a time for your call.', $form, 'consultation', array( array( 'Consult via WhatsApp', VV_WA, 'vv-outline' ) ) );
	$c   .= vv_band2( 'Start with a ' . vv_mark( 'single frame.' ), 'Browse Aurora, Aurum and Anima, the three Vivere collections.', array( array( 'Explore Frames', '/frames/', 'vv-light' ) ) );
	return $c;
}

function vv_page_experiences( $m, $j ) {
	$c = vv_hero(
		array(
			'tone'    => 'plum',
			'tag'     => 'Experiences',
			'title'   => 'Vivere is always ' . vv_mark( 'becoming.' ),
			'lead'    => 'We launched with intention and we are building with the same. Here is what is on the horizon. Register your interest and be the first to know when each experience opens.',
			'buttons' => array( array( 'Join the waitlist', '#register', 'vv-light' ), array( 'See what is coming', '#programs', 'vv-outline-light' ) ),
			'media'   => array(
				vv_img( $m['home-comingSoon-soiree'], 'Friends laughing at a garden party', 'vv-phero-img vv-phero-img--a' ),
				vv_img( $m['home-comingSoon-twenty-one-days'], 'A woman smiling in the sun', 'vv-phero-img vv-phero-img--b' ),
				vv_img( $m['home-comingSoon-regal-essence'], 'Ann Uramu', 'vv-phero-img vv-phero-img--c' ),
			),
			'stats'   => array( array( '21', 'days of transformation' ), array( '12', 'weeks of Regal Essence' ) ),
			'word'    => 'Becoming',
		)
	);
	$items = array(
		array( 'twenty-one-days', 'Program · Coming soon', '21 Days of Transformation', 'Twenty-one days. One decision. A completely different you.', array( 'A focused, immersive 21-day personal transformation program designed to interrupt your patterns, install new ones and give you a tangible experience of what intentional living actually feels like.', 'In 21 days, more is possible than most people achieve in a year, when the right framework is in place.' ), 'Join the waitlist', 'home-comingSoon-twenty-one-days', 'white' ),
		array( 'regal-essence', 'Program for women · 12 weeks', 'Cultivating Regal Essence', 'Becoming the woman you were always meant to be.', array( 'A 12-week program exclusively for women, on building elegance, poise and regal living from the inside out. Regality is not an aesthetic. It is an internal posture: the way a woman carries herself when she knows who she is, what she is worth, and what she will and will not accept.', 'For the woman who is ready to stop shrinking, stop settling and begin living a life that is intentional and exceptionally her own.' ), 'Join the waitlist', 'home-comingSoon-regal-essence', 'night' ),
		array( 'soiree', 'An intimate event · First edition', 'Vivere Soirée', 'What\'s not to love about you?', array( 'An evening of introspection, visualisation and becoming, dedicated entirely to the most important person in the room: you.', 'A curated, elegant gathering where you will walk through a personal discovery journey, explore the depths of your own identity and leave with a clearer, more grounded sense of who you are. You will come curious. You will leave knowing.' ), 'Register interest', 'home-comingSoon-soiree', 'plum' ),
	);
	$cards = '';
	foreach ( $items as $k => $it ) {
		$dark  = 'white' !== $it[7];
		$body  = vv_p( sprintf( '%02d', $k + 1 ) . ' / 03', 'vv-stack-count' );
		$body .= vv_p( vv_e( $it[1] ), 'vv-kicker' ) . vv_h( vv_e( $it[2] ), 2, 'vv-sec-title' ) . vv_p( vv_e( $it[3] ), 'vv-note' );
		foreach ( $it[4] as $para ) { $body .= vv_p( vv_e( $para ) ); }
		$body .= vv_p( 'Tickets and dates will be announced to the waitlist first.', 'vv-status' );
		$body .= vv_buttons( array( array( $it[5], '#register', $dark ? 'vv-light' : '' ) ) );
		$cards .= vv_stack_card( vv_img( $m[ $it[6] ], $it[2], 'vv-stack-img' ), $body, $it[7], $it[0] );
	}
	$c   .= vv_section( vv_group( vv_h( 'Three ways to ' . vv_mark( 'step in.' ), 2, 'vv-sec-title' ) . vv_p( 'Programs and gatherings, each built around one idea: becoming, on purpose.', 'vv-lead' ), 'vv-head' ) . vv_group( $cards, 'vv-stack' ), 'vv-stack-sec', 'programs' );
	$form = vv_form( 'Experience waitlist', vv_contact_fields( array( array( 'select', 'experience', 'Experience', array( '21 Days of Transformation', 'Cultivating Regal Essence', 'Vivere Soirée', 'All of them' ) ) ) ), 'Register my interest' );
	$c   .= vv_order_section( 'Be first ' . vv_mark( 'in the room.' ), 'Registered members receive priority access and preferential rates when each experience opens. Private group and corporate programs are also available.', $form, 'register' );
	return $c;
}

function vv_page_book( $m, $j ) {
	$c = vv_hero(
		array(
			'tone'    => 'night',
			'tag'     => 'Coming soon',
			'title'   => 'The Vivere ' . vv_mark( 'book.' ),
			'lead'    => 'Ann Uramu\'s new book, with a private online workbook you read and work through on Vivere.',
			'buttons' => array( array( 'Read chapter one free', '/read/becoming-by-design/', 'vv-light' ), array( 'Join the waitlist', '#join', 'vv-outline-light' ) ),
			'media'   => array(
				vv_theme_img( 'journal.jpg', 'A woman writing in a journal at a desk', 'vv-phero-img vv-phero-img--a' ),
				vv_theme_img( 'hero-3.jpg', 'An open book', 'vv-phero-img vv-phero-img--b' ),
			),
			'stats'   => array( array( '4', 'workbook tools in every chapter' ) ),
			'word'    => 'Workbook',
		)
	);
	$mock  = '<div class="vv-mock" aria-hidden="true">';
	$mock .= '<div class="vv-mock-top"><span></span><span></span><span></span><b>My workbook</b></div>';
	$mock .= '<div class="vv-mock-body"><p class="vv-mock-label">Your progress</p><div class="vv-mock-ring" style="--p:64"><strong>64%</strong></div>';
	foreach ( array( array( 'Chapter 1', 100 ), array( 'Chapter 2', 100 ), array( 'Chapter 3', 72 ), array( 'Chapter 4', 18 ) ) as $ch ) {
		$mock .= '<div class="vv-mock-row"><span>' . $ch[0] . '</span><i style="--w:' . $ch[1] . '%"></i></div>';
	}
	$mock .= '<ul class="vv-mock-list"><li class="is-done">Reflection saved</li><li class="is-done">Action point done</li><li>Journal entry</li></ul></div></div>';
	$body  = vv_h( 'Read it. Then do the work, ' . vv_mark( 'chapter by chapter.' ), 2, 'vv-sec-title' );
	$body .= vv_p( 'The book will live on Vivere, in your own private space. Each chapter comes with a workbook so the ideas turn into decisions, and the decisions into a different life.' );
	$body .= vv_p( 'Access is for members only. The book is read on the site and is never downloaded.', 'vv-note' );
	$body .= vv_buttons( array( array( 'Open the reader', '/read/becoming-by-design/', '' ), array( 'Join the waitlist', '#join', 'vv-outline' ) ) );
	$c    .= vv_section( vv_group( vv_group( vv_html( $mock ), 'vv-mock-wrap' ) . vv_group( $body, 'vv-feature-body' ), 'vv-feature' ), 'vv-sec--tight' );

	$feats = array(
		array( 'A journal per chapter', 'Write as you read. Every subject has its own private journal, kept in your account.' ),
		array( 'Reflections', 'Guided questions at the end of each chapter, so you can see what the chapter changed in you.' ),
		array( 'Action points', 'Turn each insight into a small, specific step, and tick it off when it is done.' ),
		array( 'Progress tracker', 'See how far you have come across the book, the workbook and your action points.' ),
	);
	$cards = '';
	foreach ( $feats as $n => $f ) {
		$cards .= vv_group( vv_p( sprintf( '%02d', $n + 1 ), 'vv-card-num' ) . vv_h( vv_e( $f[0] ), 3 ) . vv_p( vv_e( $f[1] ) ), 'vv-card vv-card--icon vv-card--i' . ( $n + 1 ) );
	}
	$c .= vv_section( vv_group( vv_h( 'Inside the ' . vv_mark( 'workbook.' ), 2, 'vv-sec-title' ) . vv_p( 'Four tools that turn reading into change.', 'vv-lead' ), 'vv-head' ) . vv_group( $cards, 'vv-cards' ), 'vv-sand', 'workbook' );
	$c .= '<!-- wp:pattern {"slug":"vivere/newsletter"} /-->' . "\n\n";
	return $c;
}

function vv_page_contact( $m, $j ) {
	$c = vv_hero(
		array(
			'tone'    => 'ink',
			'short'   => true,
			'tag'     => 'Get in touch',
			'title'   => 'We would love to ' . vv_mark( 'hear from you.' ),
			'lead'    => 'A question about a product, a corporate or group booking, or simply finding out more about the Vivere world. We respond within 48 hours.',
			'buttons' => array( array( 'Send a message', '#form', 'vv-light' ), array( 'WhatsApp the concierge', VV_WA, 'vv-outline-light' ) ),
			'media'   => array(
				vv_theme_img( 'hero-5.jpg', 'Friends raising glasses together', 'vv-phero-img vv-phero-img--a' ),
				vv_theme_img( 'join.jpg', 'Friends talking around a table', 'vv-phero-img vv-phero-img--b' ),
			),
			'stats'   => array( array( '48h', 'response time, Monday to Friday' ) ),
			'word'    => 'Hello',
		)
	);
	$tiles  = vv_group( vv_p( 'Email', 'vv-tile-label' ) . vv_p( '<a href="mailto:concierge@thevivereexperience.com">concierge@thevivereexperience.com</a>', 'vv-tile-big' ), 'vv-ctile vv-ctile--mail' );
	$tiles .= vv_group( vv_p( 'WhatsApp', 'vv-tile-label' ) . vv_p( '<a href="' . VV_WA . '">Message the concierge</a>', 'vv-tile-big' ), 'vv-ctile vv-ctile--wa', 'div', array( 'anchor' => 'whatsapp' ) );
	$tiles .= vv_group( vv_p( 'Response time', 'vv-tile-label' ) . vv_p( 'Within 48 hours, Monday to Friday.', 'vv-tile-big' ), 'vv-ctile vv-ctile--time' );
	$c     .= vv_section( vv_group( $tiles, 'vv-tiles' ), 'vv-sec--tight vv-tiles-sec' );
	$info   = vv_h( 'Send us ' . vv_mark( 'a message.' ), 2, 'vv-sec-title' );
	$info  .= vv_p( 'Tell us what you are looking for and the right person on the team will come back to you.', 'vv-lead' );
	$info  .= vv_quote( '“Vivere Global is more than a brand. It is an invitation to live intentionally, exceptionally and profoundly. We are glad you are here.”', 'The Vivere team', 'vv-quote vv-quote--pop' );
	$form   = vv_form( 'Contact', vv_contact_fields( array( array( 'select', 'subject', 'What is it about?', array( 'A general question', 'Frames', 'Books', 'Space consultation', 'Programs and events', 'Corporate or group booking', 'Press and speaking' ) ) ) ), 'Send message' );
	$c     .= vv_section( vv_group( vv_group( $info, 'vv-order-copy' ) . $form, 'vv-split vv-form-wrap' ), 'vv-order', 'form' );
	return $c;
}

/** Convert the old journal markdown to blocks (paragraphs, quotes, headings, images). */
function vv_md_to_blocks( $md ) {
	$out = '';
	foreach ( preg_split( "/\n{2,}/", trim( $md ) ) as $chunk ) {
		$chunk = trim( $chunk );
		if ( '' === $chunk ) { continue; }
		$inline = function ( $t ) {
			$t = vv_e( vv_nodash( $t ) );
			$t = preg_replace( '/\*\*(.+?)\*\*/', '<strong>$1</strong>', $t );
			$t = preg_replace( '/\*(.+?)\*/', '$1', $t ); // No italics in this design.
			return $t;
		};
		if ( preg_match( '/^(\x{2014}|-{3,}|\*{3,})$/u', $chunk ) ) {
			$out .= "<!-- wp:separator -->\n<hr class=\"wp-block-separator has-alpha-channel-opacity\"/>\n<!-- /wp:separator -->\n\n";
			continue;
		}
		if ( preg_match( '/^!\[(.*?)\]\((.+?)\)$/', $chunk, $mm ) ) {
			$out .= "<!-- wp:image {\"sizeSlug\":\"large\",\"linkDestination\":\"none\"} -->\n<figure class=\"wp-block-image size-large\"><img src=\"" . esc_url( $mm[2] ) . '" alt="' . vv_a( $mm[1] ) . "\"/></figure>\n<!-- /wp:image -->\n\n";
		} elseif ( preg_match( '/^(#{2,4})\s+(.+)$/', $chunk, $mm ) ) {
			$out .= vv_h( $inline( $mm[2] ), strlen( $mm[1] ) );
		} elseif ( 0 === strpos( $chunk, '>' ) ) {
			$t    = trim( preg_replace( '/^>\s?/m', '', $chunk ) );
			$cite = '';
			if ( preg_match( '/^(.*?)\s+[—-]\s+([^"”]+)$/u', $t, $mm ) ) { $t = $mm[1]; $cite = $mm[2]; }
			$out .= '<!-- wp:quote -->' . "\n<blockquote class=\"wp-block-quote\">" . vv_p( $inline( $t ) ) . ( $cite ? '<cite>' . vv_e( $cite ) . '</cite>' : '' ) . "</blockquote>\n<!-- /wp:quote -->\n\n";
		} else {
			$out .= vv_p( nl2br( $inline( $chunk ), false ) );
		}
	}
	return $out;
}

function vv_import_journal( $m, $j ) {
	$ids = array();
	foreach ( $j['blog'] as $p ) {
		$ex = get_page_by_path( $p['slug'], OBJECT, 'post' );
		if ( $ex ) { $ids[] = $ex->ID; continue; } // Keep edits made in WordPress.
		$id = wp_insert_post(
			array(
				'ID'           => $ex ? $ex->ID : 0,
				'post_type'    => 'post',
				'post_status'  => empty( $p['published'] ) ? 'draft' : 'publish',
				'post_title'   => $p['title'],
				'post_name'    => $p['slug'],
				'post_excerpt' => vv_nodash( $p['excerpt'] ),
				'post_date'    => $p['date'] . ' 09:00:00',
				'post_content' => vv_md_to_blocks( $p['body'] ),
				'tags_input'   => isset( $p['tags'] ) ? $p['tags'] : array(),
			)
		);
		if ( $id && ! is_wp_error( $id ) && ! empty( $m[ 'blog-' . $p['slug'] ] ) ) {
			set_post_thumbnail( $id, $m[ 'blog-' . $p['slug'] ] );
		}
		$ids[] = $id;
	}
	return $ids;
}

function vivere_build_pages() {
	$m = get_option( 'vivere_media_map', array() );
	$j = get_option( 'vivere_legacy_content', array() );
	foreach ( array( 'framePricing', 'revamp', 'blog', 'testimonials', 'faq', 'homepage' ) as $k ) {
		if ( isset( $j[ $k ] ) && is_string( $j[ $k ] ) ) { $j[ $k ] = json_decode( $j[ $k ], true ); }
	}
	$out = array(
		'about'       => vv_upsert_page( 'about', 'About', vv_page_about( $m, $j ) ),
		'frames'      => vv_upsert_page( 'frames', 'Frames', vv_page_frames( $m, $j ) ),
		'reads'       => vv_upsert_page( 'reads', 'Reads', vv_page_reads( $m, $j ) ),
		'spaces'      => vv_upsert_page( 'spaces', 'Spaces', vv_page_spaces( $m, $j ) ),
		'experiences' => vv_upsert_page( 'experiences', 'Experiences', vv_page_experiences( $m, $j ) ),
		'book'        => vv_upsert_page( 'book', 'The Vivere book', vv_page_book( $m, $j ) ),
		'contact'     => vv_upsert_page( 'contact', 'Contact', vv_page_contact( $m, $j ) ),
	);
	$journal = get_page_by_path( 'journal' );
	$jid     = wp_insert_post( array( 'ID' => $journal ? $journal->ID : 0, 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => 'Journal', 'post_name' => 'journal' ) );
	update_option( 'page_for_posts', $jid );
	$out['journal'] = $jid;
	$out['posts']   = vv_import_journal( $m, $j );
	return $out;
}
