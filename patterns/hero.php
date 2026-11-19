<?php
/**
 * Title: Hero slider
 * Slug: vivere/hero
 * Categories: vivere, featured
 * Keywords: hero, slider, banner
 * Viewport Width: 1440
 * Description: Full-bleed photo slider. Each slide is a group with an image, a label, a title, a line of text and buttons. Add, remove or reorder slides in the List View.
 *
 * @package Vivere
 */

$vivere_slides = array(
	array( 'hero-1.jpg', 'Woman in a calm, sunlit lifestyle portrait', 'Vivere Global', 'Experiencing life profoundly', 'A lifestyle and personal transformation brand for people ready to stop living on autopilot.', array( array( 'Explore Vivere', '#explore', 'vv-light' ), array( 'Join the Vivere World', '#join', 'vv-outline-light' ) ) ),
	array( 'hero-2.jpg', 'Framed art above a sofa in a living room', 'Vivere Frames', 'Art that holds a room', 'Three collections of wall art, Aurora, Aurum and Anima, chosen to set the tone of a space.', array( array( 'View the collections', '/frames/', 'vv-light' ) ) ),
	array( 'hero-3.jpg', 'A stack of open books', 'Coming soon', 'The Vivere book', 'Ann Uramu\'s new book, with a private online workbook for your journal, reflections and progress.', array( array( 'Join the waitlist', '/book/', 'vv-light' ) ) ),
	array( 'hero-4.jpg', 'A styled modern living room', 'Vivere Spaces', 'Rooms made with intention', 'A consultation service that reworks the spaces you live and work in.', array( array( 'Book a consultation', '/spaces/#consultation', 'vv-light' ) ) ),
	array( 'hero-5.jpg', 'A candlelit dinner table set for an evening event', 'Vivere Soirée', 'An evening worth dressing for', 'Our first ticketed experience. Register your interest to hear first when tickets open.', array( array( 'Register interest', '/experiences/', 'vv-light' ) ) ),
);
?>
<!-- wp:group {"tagName":"section","align":"full","className":"vv-hero","layout":{"type":"default"}} -->
<section class="wp-block-group alignfull vv-hero">
<?php foreach ( $vivere_slides as $vivere_i => $vivere_s ) : ?>
<!-- wp:group {"className":"vv-slide","layout":{"type":"default"}} -->
<div class="wp-block-group vv-slide"><!-- wp:image {"sizeSlug":"full","linkDestination":"none","className":"vv-slide-img"} -->
<figure class="wp-block-image size-full vv-slide-img"><img src="<?php echo vivere_img( $vivere_s[0] ); ?>" alt="<?php echo esc_attr( $vivere_s[1] ); ?>"/></figure>
<!-- /wp:image -->

<!-- wp:group {"className":"vv-hero-copy","layout":{"type":"default"}} -->
<div class="wp-block-group vv-hero-copy"><!-- wp:paragraph {"className":"vv-label"} -->
<p class="vv-label"><?php echo esc_html( $vivere_s[2] ); ?></p>
<!-- /wp:paragraph -->

<?php if ( 0 === $vivere_i ) : ?>
<!-- wp:heading {"level":1,"className":"vv-hero-title"} -->
<h1 class="wp-block-heading vv-hero-title"><?php echo esc_html( $vivere_s[3] ); ?></h1>
<!-- /wp:heading -->
<?php else : ?>
<!-- wp:heading {"className":"vv-hero-title"} -->
<h2 class="wp-block-heading vv-hero-title"><?php echo esc_html( $vivere_s[3] ); ?></h2>
<!-- /wp:heading -->
<?php endif; ?>

<!-- wp:paragraph {"className":"vv-hero-text"} -->
<p class="vv-hero-text"><?php echo esc_html( $vivere_s[4] ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:buttons {"className":"vv-hero-ctas"} -->
<div class="wp-block-buttons vv-hero-ctas"><?php foreach ( $vivere_s[5] as $vivere_b ) : ?><!-- wp:button {"className":"is-style-<?php echo esc_attr( $vivere_b[2] ); ?>"} -->
<div class="wp-block-button is-style-<?php echo esc_attr( $vivere_b[2] ); ?>"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( $vivere_b[1] ); ?>"><?php echo esc_html( $vivere_b[0] ); ?></a></div>
<!-- /wp:button --><?php endforeach; ?></div>
<!-- /wp:buttons --></div>
<!-- /wp:group --></div>
<!-- /wp:group -->
<?php endforeach; ?>
</section>
<!-- /wp:group -->
