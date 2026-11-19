/**
 * Vivere motion layer: page heroes, scroll reveals, split headings, parallax,
 * stacking cards, counters, ribbons and hover effects. Uses GSAP + ScrollTrigger + SplitText.
 * Everything degrades to a static, fully visible page without JS or with reduced motion.
 */
( function () {
	'use strict';
	var d = document;
	var root = d.documentElement;
	var hoverable = window.matchMedia( '(hover: hover) and (pointer: fine)' ).matches;
	var reduce = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
	var $ = function ( s, c ) { return ( c || d ).querySelector( s ); };
	var $$ = function ( s, c ) { return [].slice.call( ( c || d ).querySelectorAll( s ) ); };

	/* ---------- no-GSAP features ---------- */
	function initHeader() {
		var h = $( '.vv-header--overlay' );
		if ( ! h ) { return; }
		var hero = $( '.vv-phero, .vv-hero' );
		var on = false;
		var check = function () {
			var limit = hero ? Math.max( 160, hero.offsetHeight * 0.55 ) : 160;
			var s = window.scrollY > limit;
			if ( s !== on ) { on = s; h.classList.toggle( 'is-stuck', s ); }
		};
		window.addEventListener( 'scroll', check, { passive: true } );
		check();
	}

	function initProgress() {
		var bar = d.createElement( 'div' );
		bar.className = 'vv-progress';
		bar.setAttribute( 'aria-hidden', 'true' );
		d.body.appendChild( bar );
		var tick = false;
		var draw = function () {
			var max = root.scrollHeight - window.innerHeight;
			bar.style.transform = 'scaleX(' + ( max > 0 ? Math.min( 1, window.scrollY / max ) : 0 ) + ')';
			tick = false;
		};
		window.addEventListener( 'scroll', function () { if ( ! tick ) { tick = true; requestAnimationFrame( draw ); } }, { passive: true } );
		draw();
	}

	function initRibbons() {
		$$( '.vv-marquee-track, .vv-tmarquee-track' ).forEach( function ( t ) {
			if ( t.getAttribute( 'data-cloned' ) ) { return; }
			$$( ':scope > *', t ).forEach( function ( k ) {
				var c = k.cloneNode( true );
				c.setAttribute( 'aria-hidden', 'true' );
				t.appendChild( c );
			} );
			t.setAttribute( 'data-cloned', '1' );
		} );
	}

	function initTilt() {
		if ( ! hoverable || reduce ) { return; }
		$$( '.vv-book' ).forEach( function ( card ) {
			var cover = $( '.vv-book-cover', card );
			if ( ! cover ) { return; }
			card.addEventListener( 'mousemove', function ( e ) {
				var r = cover.getBoundingClientRect();
				var x = ( e.clientX - r.left ) / r.width - 0.5;
				var y = ( e.clientY - r.top ) / r.height - 0.5;
				cover.style.transform = 'perspective(800px) rotateY(' + ( x * 16 ).toFixed( 2 ) + 'deg) rotateX(' + ( -y * 12 ).toFixed( 2 ) + 'deg) translateY(-8px)';
			} );
			card.addEventListener( 'mouseleave', function () { cover.style.transform = ''; } );
		} );
	}

	function initMagnetic() {
		if ( ! hoverable || reduce ) { return; }
		$$( '.vv-phero-ctas .wp-block-button__link, .vv-band2-ctas .wp-block-button__link' ).forEach( function ( b ) {
			b.addEventListener( 'mousemove', function ( e ) {
				var r = b.getBoundingClientRect();
				b.style.transform = 'translate(' + ( ( e.clientX - r.left - r.width / 2 ) * 0.22 ).toFixed( 1 ) + 'px,' + ( ( e.clientY - r.top - r.height / 2 ) * 0.32 ).toFixed( 1 ) + 'px)';
			} );
			b.addEventListener( 'mouseleave', function () { b.style.transform = ''; } );
		} );
	}

	/* ---------- GSAP ---------- */
	function countUp( el, delay ) {
		var g = window.gsap;
		var txt = el.textContent.trim();
		var m = txt.match( /^(\d+)(.*)$/ );
		if ( ! m ) { return; }
		var end = parseInt( m[ 1 ], 10 );
		var o = { v: 0 };
		el.textContent = '0' + m[ 2 ];
		g.to( o, { v: end, duration: 1.6, delay: delay || 0, ease: 'power3.out', onUpdate: function () { el.textContent = Math.round( o.v ) + m[ 2 ]; } } );
	}

	function split( el, type ) {
		if ( ! window.SplitText ) { return null; }
		try {
			return new window.SplitText( el, { type: type || 'lines,words', linesClass: 'vv-line', mask: 'lines' } );
		} catch ( e ) { return null; }
	}

	function motion() {
		var g = window.gsap;
		var ST = window.ScrollTrigger;
		if ( ! g || ! ST || reduce ) { root.classList.remove( 'vv-motion' ); return; }
		window.vvMotion = true;
		g.registerPlugin( ST );
		if ( window.SplitText ) { g.registerPlugin( window.SplitText ); }
		var ease = 'expo.out';

		/* page hero */
		var hero = $( '.vv-phero' );
		if ( hero ) {
			var copy = $( '.vv-phero-copy', hero );
			var media = $( '.vv-phero-media', hero );
			var word = $( '.vv-phero-word', hero );
			g.set( [ copy, media, word ], { visibility: 'visible' } );
			var tl = g.timeline( { defaults: { ease: ease } } );
			var title = $( '.vv-phero-title', hero );
			var st = title ? split( title ) : null;
			if ( st ) {
				tl.from( st.words, { yPercent: 115, duration: 1.3, stagger: 0.055 }, 0.15 );
			} else if ( title ) {
				tl.from( title, { y: 40, autoAlpha: 0, duration: 1.1 }, 0.15 );
			}
			tl.from( $( '.vv-phero-tag', hero ), { y: 16, autoAlpha: 0, duration: 0.8 }, 0 )
				.from( $$( '.vv-phero-lead, .vv-phero-ctas .wp-block-button', hero ), { y: 26, autoAlpha: 0, duration: 1, stagger: 0.08 }, 0.55 )
				.fromTo( $$( '.vv-phero-img', hero ), { clipPath: 'inset(100% 0% 0% 0% round 22px)' }, { clipPath: 'inset(0% 0% 0% 0% round 22px)', duration: 1.5, stagger: 0.16, ease: 'expo.inOut' }, 0.1 )
				.from( $$( '.vv-phero-img img', hero ), { scale: 1.4, duration: 2, stagger: 0.16 }, 0.1 );
			var chip = $( '.vv-phero-chip', hero );
			if ( chip ) {
				tl.from( chip, { y: 50, autoAlpha: 0, scale: 0.9, duration: 1.1 }, 1.05 );
				$$( '.vv-stat-num', chip ).forEach( function ( n ) { countUp( n, 1.15 ); } );
			}
			if ( word ) { tl.from( word, { yPercent: 50, autoAlpha: 0, duration: 1.8 }, 0.35 ); }
			tl.add( function () { $$( 'mark.vv-hl', hero ).forEach( function ( mk ) { mk.classList.add( 'is-on' ); } ); }, 0.95 );

			var scrub = { trigger: hero, start: 'top top', end: 'bottom top', scrub: true };
			if ( word ) { g.to( word, { xPercent: -14, ease: 'none', scrollTrigger: scrub } ); }
			$$( '.vv-phero-img', hero ).forEach( function ( im, i ) {
				g.to( im, { yPercent: [ -8, -20, -32 ][ i ] || -10, ease: 'none', scrollTrigger: { trigger: hero, start: 'top top', end: 'bottom top', scrub: true } } );
			} );
			g.to( copy, { yPercent: 14, autoAlpha: 0.35, ease: 'none', scrollTrigger: { trigger: hero, start: 'top top', end: 'bottom top', scrub: true } } );
		}

		/* split section titles */
		$$( '.vv-sec-title, .vv-band2-title' ).forEach( function ( h ) {
			if ( h.closest( '.vv-hero' ) ) { return; }
			var s = split( h );
			var trig = { trigger: h, start: 'top 88%', once: true };
			if ( s ) {
				g.fromTo( s.words, { yPercent: 115 }, { yPercent: 0, duration: 1.15, ease: ease, stagger: 0.045, scrollTrigger: trig } );
			} else {
				g.fromTo( h, { y: 30, autoAlpha: 0 }, { y: 0, autoAlpha: 1, duration: 1.15, ease: ease, scrollTrigger: trig } );
			}
		} );

		/* highlights sweep in after their heading */
		ST.batch( 'mark.vv-hl', {
			start: 'top 85%',
			once: true,
			onEnter: function ( els ) { els.forEach( function ( el ) { if ( ! el.closest( '.vv-phero' ) ) { setTimeout( function () { el.classList.add( 'is-on' ); }, 450 ); } } ); }
		} );

		/* soft reveals */
		var soft = [
			'.vv-sec .vv-lead', '.vv-prose > p', '.vv-feature-body > p', '.vv-feature-body > .wp-block-buttons', '.vv-feature-body > .wp-block-quote',
			'.vv-feature-body > .vv-chips', '.vv-stack-body > :not(.vv-sec-title)', '.vv-steps li', '.vv-checks li', '.vv-faq .wp-block-details', '.vv-table',
			'.vv-form', '.vv-order-copy > .wp-block-buttons', '.vv-order-copy > .wp-block-quote', '.vv-statement-foot', '.vv-band2-text', '.vv-band2-ctas',
			'.vv-sticky > .wp-block-buttons', '.vv-intro .vv-lead', '.vv-hint', '.vv-join-copy > *', '.vv-join-form'
		].join( ',' );
		g.set( soft, { autoAlpha: 0, y: 34 } );
		ST.batch( soft, {
			start: 'top 92%',
			once: true,
			onEnter: function ( els ) { g.to( els, { autoAlpha: 1, y: 0, duration: 1, ease: ease, stagger: 0.07, overwrite: true } ); }
		} );

		/* cards pop in */
		var cards = '.vv-card, .vv-book, .vv-flow-step, .vv-tile, .vv-ctile, .vv-posts .wp-block-post, .vv-bento > *, .vv-event, .vv-pillar';
		g.set( cards, { autoAlpha: 0, y: 60, scale: 0.96 } );
		ST.batch( cards, {
			start: 'top 94%',
			once: true,
			onEnter: function ( els ) { g.to( els, { autoAlpha: 1, y: 0, scale: 1, duration: 1.1, ease: ease, stagger: 0.09, overwrite: true } ); }
		} );

		/* images: scrubbed reveal + parallax */
		$$( '.vv-feature-img, .vv-stack-img, .vv-join-img, .vv-founder-img' ).forEach( function ( fig ) {
			var img = $( 'img', fig );
			if ( fig.closest( '.vv-join' ) ) {
				if ( img ) { g.fromTo( img, { yPercent: -8, scale: 1.18 }, { yPercent: 8, scale: 1.18, ease: 'none', scrollTrigger: { trigger: fig.parentNode, start: 'top bottom', end: 'bottom top', scrub: true } } ); }
				return;
			}
			g.fromTo( fig, { clipPath: 'inset(12% 10% 12% 10% round 30px)' }, { clipPath: 'inset(0% 0% 0% 0% round 22px)', ease: 'none', scrollTrigger: { trigger: fig, start: 'top 96%', end: 'top 40%', scrub: 0.6 } } );
			if ( img ) { g.fromTo( img, { scale: 1.3, yPercent: -6 }, { scale: 1.08, yPercent: 6, ease: 'none', scrollTrigger: { trigger: fig, start: 'top bottom', end: 'bottom top', scrub: true } } ); }
		} );

		/* statement: words light up as you read */
		$$( '.vv-statement' ).forEach( function ( el ) {
			var s = split( el, 'words' );
			if ( ! s ) { return; }
			g.fromTo( s.words, { opacity: 0.14 }, { opacity: 1, stagger: 0.12, ease: 'none', scrollTrigger: { trigger: el, start: 'top 82%', end: 'bottom 45%', scrub: true } } );
		} );

		/* class toggles: strike words, workbook preview, flow line */
		$$( '.vv-words, .vv-mock' ).forEach( function ( el ) {
			ST.create( { trigger: el, start: 'top 80%', once: true, onEnter: function () { el.classList.add( 'is-on' ); } } );
		} );
		$$( '.vv-mock-ring' ).forEach( function ( ring ) {
			var end = parseFloat( getComputedStyle( ring ).getPropertyValue( '--p' ) ) || 0;
			var label = $( 'strong', ring );
			var o = { v: 0 };
			ring.style.setProperty( '--pp', 0 );
			ST.create( { trigger: ring, start: 'top 85%', once: true, onEnter: function () {
				g.to( o, { v: end, duration: 1.8, ease: 'power3.out', onUpdate: function () { ring.style.setProperty( '--pp', o.v ); if ( label ) { label.textContent = Math.round( o.v ) + '%'; } } } );
			} } );
		} );
		$$( '.vv-flow' ).forEach( function ( f ) {
			g.fromTo( f, { '--flow': 0 }, { '--flow': 1, ease: 'none', scrollTrigger: { trigger: f, start: 'top 80%', end: 'bottom 70%', scrub: true } } );
		} );

		/* counters outside the hero */
		$$( '.vv-stat-num' ).forEach( function ( n ) {
			if ( n.closest( '.vv-phero' ) ) { return; }
			ST.create( { trigger: n, start: 'top 90%', once: true, onEnter: function () { countUp( n ); } } );
		} );

		/* stacking cards (desktop) */
		var mm = g.matchMedia();
		mm.add( '(min-width: 1081px)', function () {
			$$( '.vv-stack' ).forEach( function ( stack ) {
				var list = $$( ':scope > .vv-stack-card', stack );
				list.forEach( function ( card, i ) {
					var next = list[ i + 1 ];
					if ( ! next ) { return; }
					g.to( card, { scale: 0.9, filter: 'brightness(0.78)', ease: 'none', scrollTrigger: { trigger: next, start: 'top bottom', end: 'top 92px', scrub: true } } );
				} );
			} );
		} );

		window.addEventListener( 'load', function () { ST.refresh(); } );
	}

	function boot() {
		initProgress();
		initRibbons();
		initTilt();
		initMagnetic();
		motion();
	}
	if ( d.readyState === 'loading' ) { d.addEventListener( 'DOMContentLoaded', boot ); } else { boot(); }
}() );
