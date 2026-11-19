<?php
/**
 * Title: About intro with pillars
 * Slug: vivere/intro
 * Categories: vivere, about
 * Viewport Width: 1440
 *
 * @package Vivere
 */

$vivere_pillars = array(
	array( 'frames', 'Vivere Frames', '/frames/', 'Wall art in three collections: Aurora, Aurum and Anima.' ),
	array( 'reads', 'Vivere Reads', '/reads/', 'The books Ann Uramu recommends, and why each one matters.' ),
	array( 'spaces', 'Vivere Spaces', '/spaces/', 'A consultation service that reworks the rooms you live in.' ),
	array( 'events', 'Programs and events', '/experiences/', 'Transformation programs and the Vivere Soirée.' ),
);
?>
<!-- wp:group {"tagName":"section","align":"full","className":"vv-sec vv-intro","layout":{"type":"constrained"}} -->
<section class="wp-block-group alignfull vv-sec vv-intro"><!-- wp:group {"className":"vv-split","layout":{"type":"default"}} -->
<div class="wp-block-group vv-split"><!-- wp:group {"layout":{"type":"default"}} -->
<div class="wp-block-group"><!-- wp:paragraph {"className":"vv-kicker"} -->
<p class="vv-kicker">About Vivere</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"className":"vv-intro-title"} -->
<h2 class="wp-block-heading vv-intro-title">Vivere is a lifestyle and personal transformation brand for people who are <strong>done living on autopilot</strong> and ready to live with intention.</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"vv-lead"} -->
<p class="vv-lead">From the Latin vivere, to live fully. We curate the art on your walls, the books you read, the rooms you live in and the experiences that change how you see yourself.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:group {"className":"vv-pillars","layout":{"type":"default"}} -->
<div class="wp-block-group vv-pillars">
<?php foreach ( $vivere_pillars as $vivere_p ) : ?>
<!-- wp:group {"className":"vv-pillar vv-ico-<?php echo esc_attr( $vivere_p[0] ); ?>","layout":{"type":"default"}} -->
<div class="wp-block-group vv-pillar vv-ico-<?php echo esc_attr( $vivere_p[0] ); ?>"><!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading"><a href="<?php echo esc_url( $vivere_p[2] ); ?>"><?php echo esc_html( $vivere_p[1] ); ?></a></h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p><?php echo esc_html( $vivere_p[3] ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->
<?php endforeach; ?>
</div>
<!-- /wp:group --></div>
<!-- /wp:group --></section>
<!-- /wp:group -->
