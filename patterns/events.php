<?php
/**
 * Title: Upcoming events list
 * Slug: vivere/events
 * Categories: vivere
 * Viewport Width: 1440
 *
 * @package Vivere
 */

$vivere_events = array(
	array( '', 'Date', 'TBA', 'soon', 'vv-status--hot', 'Tickets opening soon', 'Vivere Soirée', 'Venue to be announced · An evening experience', 'Register interest', '', 'soiree.jpg', 'Candlelit dinner table' ),
	array( 'vv-ev-date--soft', 'Program', '21', 'days', '', 'Waitlist open', '21 Days of Transformation', 'Guided program · Online', 'Join the waitlist', 'is-style-vv-outline', 'workshop.jpg', 'Women in a workshop' ),
	array( 'vv-ev-date--soft', 'Program', 'CRE', 'for women', '', 'Waitlist open', 'Cultivating Regal Essence', 'A transformation program for women', 'Join the waitlist', 'is-style-vv-outline', 'women.jpg', 'Portrait of a woman' ),
);
?>
<!-- wp:group {"tagName":"section","align":"full","anchor":"events","className":"vv-sec vv-events","layout":{"type":"constrained"}} -->
<section id="events" class="wp-block-group alignfull vv-sec vv-events"><!-- wp:group {"className":"vv-head","layout":{"type":"default"}} -->
<div class="wp-block-group vv-head"><!-- wp:group {"layout":{"type":"default"}} -->
<div class="wp-block-group"><!-- wp:paragraph {"className":"vv-kicker"} -->
<p class="vv-kicker">Experiences</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"className":"vv-sec-title"} -->
<h2 class="wp-block-heading vv-sec-title">Upcoming programs and events.</h2>
<!-- /wp:heading --></div>
<!-- /wp:group -->

<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button {"className":"is-style-vv-outline"} -->
<div class="wp-block-button is-style-vv-outline"><a class="wp-block-button__link wp-element-button" href="/experiences/">See all experiences</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group -->

<!-- wp:group {"className":"vv-ev-list","layout":{"type":"default"}} -->
<div class="wp-block-group vv-ev-list">
<?php foreach ( $vivere_events as $vivere_e ) : ?>
<!-- wp:group {"className":"vv-event","layout":{"type":"default"}} -->
<div class="wp-block-group vv-event"><!-- wp:group {"className":"vv-ev-date<?php echo $vivere_e[0] ? ' ' . esc_attr( $vivere_e[0] ) : ''; ?>","layout":{"type":"default"}} -->
<div class="wp-block-group vv-ev-date<?php echo $vivere_e[0] ? ' ' . esc_attr( $vivere_e[0] ) : ''; ?>"><!-- wp:paragraph {"className":"vv-ev-m"} -->
<p class="vv-ev-m"><?php echo esc_html( $vivere_e[1] ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"className":"vv-ev-d"} -->
<p class="vv-ev-d"><?php echo esc_html( $vivere_e[2] ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"className":"vv-ev-y"} -->
<p class="vv-ev-y"><?php echo esc_html( $vivere_e[3] ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:group {"className":"vv-ev-body","layout":{"type":"default"}} -->
<div class="wp-block-group vv-ev-body"><!-- wp:paragraph {"className":"vv-status<?php echo $vivere_e[4] ? ' ' . esc_attr( $vivere_e[4] ) : ''; ?>"} -->
<p class="vv-status<?php echo $vivere_e[4] ? ' ' . esc_attr( $vivere_e[4] ) : ''; ?>"><?php echo esc_html( $vivere_e[5] ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading"><?php echo esc_html( $vivere_e[6] ); ?></h3>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"vv-ev-meta"} -->
<p class="vv-ev-meta"><?php echo esc_html( $vivere_e[7] ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:buttons {"className":"vv-ev-act"} -->
<div class="wp-block-buttons vv-ev-act"><!-- wp:button<?php echo $vivere_e[9] ? ' {"className":"' . esc_attr( $vivere_e[9] ) . '"}' : ''; ?> -->
<div class="wp-block-button<?php echo $vivere_e[9] ? ' ' . esc_attr( $vivere_e[9] ) : ''; ?>"><a class="wp-block-button__link wp-element-button" href="/experiences/"><?php echo esc_html( $vivere_e[8] ); ?></a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons -->

<!-- wp:image {"sizeSlug":"full","linkDestination":"none","className":"vv-ev-img"} -->
<figure class="wp-block-image size-full vv-ev-img"><img src="<?php echo vivere_img( $vivere_e[10] ); ?>" alt="<?php echo esc_attr( $vivere_e[11] ); ?>"/></figure>
<!-- /wp:image --></div>
<!-- /wp:group -->
<?php endforeach; ?>
</div>
<!-- /wp:group --></section>
<!-- /wp:group -->
