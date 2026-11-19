<?php
/**
 * Title: Join the Vivere World (newsletter)
 * Slug: vivere/newsletter
 * Categories: vivere, call-to-action
 * Viewport Width: 1440
 *
 * @package Vivere
 */
?>
<!-- wp:group {"tagName":"section","align":"full","anchor":"join","className":"vv-sec vv-join","layout":{"type":"constrained"}} -->
<section id="join" class="wp-block-group alignfull vv-sec vv-join"><!-- wp:group {"className":"vv-join-card","layout":{"type":"default"}} -->
<div class="wp-block-group vv-join-card"><!-- wp:image {"sizeSlug":"full","linkDestination":"none","className":"vv-join-img"} -->
<figure class="wp-block-image size-full vv-join-img"><img src="<?php echo vivere_img( 'join.jpg' ); ?>" alt="A group of women laughing together"/></figure>
<!-- /wp:image -->

<!-- wp:group {"className":"vv-join-copy","layout":{"type":"default"}} -->
<div class="wp-block-group vv-join-copy"><!-- wp:heading -->
<h2 class="wp-block-heading">Join the Vivere World.</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Quiet, considered updates. Never noise. Be first to hear when tickets, collections and the book go live.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:html -->
<form class="vv-join-form" method="post" action="/wp-admin/admin-post.php">
	<input type="hidden" name="action" value="vivere_join">
	<label for="vv-join-name">First name</label>
	<input id="vv-join-name" name="vv_name" type="text" autocomplete="given-name" placeholder="Your first name">
	<label for="vv-join-email">Email</label>
	<input id="vv-join-email" name="vv_email" type="email" autocomplete="email" placeholder="you@example.com" required>
	<input class="vv-hp" type="text" name="vv_website" tabindex="-1" autocomplete="off" aria-hidden="true">
	<button class="vv-btn-light" type="submit">Subscribe</button>
	<p class="vv-fine" role="status">You can unsubscribe at any time.</p>
</form>
<!-- /wp:html --></div>
<!-- /wp:group --></section>
<!-- /wp:group -->
