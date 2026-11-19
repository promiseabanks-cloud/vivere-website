/* Vivere header: dynamic island on scroll + full-page menu. No dependencies. */
( function () {
	'use strict';
	var d = document;
	var cfg = window.vvNav || {};
	var img = cfg.img || '';
	var reduce = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

	var LINKS = [
		{ t: 'Home', u: '/', i: 'hero-2.jpg', s: 'Where it all begins' },
		{ t: 'Frames', u: '/frames/', i: 'frames.jpg', s: 'Words and faces for your walls' },
		{ t: 'Reads', u: '/reads/', i: 'reads.jpg', s: 'Twenty books, personally read' },
		{ t: 'Spaces', u: '/spaces/', i: 'spaces.jpg', s: 'Rooms made with intention' },
		{ t: 'Experiences', u: '/experiences/', i: 'soiree.jpg', s: 'Programs and gatherings' },
		{ t: 'The Book', u: '/book/', i: 'journal.jpg', s: 'Read it, then do the work' },
		{ t: 'Journal', u: '/journal/', i: 'workshop.jpg', s: 'Reflections on living by design' },
		{ t: 'About', u: '/about/', i: 'hero-1.jpg', s: 'Built from the inside out' },
		{ t: 'Contact', u: '/contact/', i: 'hero-5.jpg', s: 'We would love to hear from you' }
	];

	function el( tag, cls, html ) {
		var e = d.createElement( tag );
		if ( cls ) { e.className = cls; }
		if ( html ) { e.innerHTML = html; }
		return e;
	}
	function here( u ) {
		var p = location.pathname.replace( /\/+$/, '/' );
		return u === '/' ? p === '/' : p.indexOf( u ) === 0;
	}

	/* ---------- dynamic island ---------- */
	function initIsland() {
		var h = d.querySelector( '.vv-header' );
		if ( ! h ) { return; }
		var overlay = h.classList.contains( 'vv-header--overlay' );
		var wrap = h.parentNode;
		var on = false;
		var check = function () {
			var y = window.scrollY;
			var hero = d.querySelector( '.vv-phero, .vv-hero' );
			var limit = hero ? Math.max( 140, Math.min( hero.offsetHeight * 0.45, 420 ) ) : 120;
			var s = y > limit;
			if ( s !== on ) {
				on = s;
				if ( s && ! overlay && wrap ) { wrap.style.minHeight = h.offsetHeight + 'px'; }
				h.classList.toggle( 'is-island', s );
				if ( ! s && wrap ) { wrap.style.minHeight = ''; }
			}
		};
		var tick = false;
		window.addEventListener( 'scroll', function () {
			if ( ! tick ) { tick = true; requestAnimationFrame( function () { check(); tick = false; } ); }
		}, { passive: true } );
		check();
	}

	/* ---------- full-page menu ---------- */
	function initMenu() {
		var h = d.querySelector( '.vv-header' );
		var inner = h && h.querySelector( '.vv-header-inner' );
		if ( ! inner ) { return; }

		var burger = el( 'button', 'vv-burger', '<span></span><span></span><span class="screen-reader-text">Open menu</span>' );
		burger.type = 'button';
		burger.setAttribute( 'aria-expanded', 'false' );
		burger.setAttribute( 'aria-controls', 'vv-menu' );
		inner.appendChild( burger );

		var menu = el( 'div', 'vv-menu' );
		menu.id = 'vv-menu';
		menu.setAttribute( 'role', 'dialog' );
		menu.setAttribute( 'aria-modal', 'true' );
		menu.setAttribute( 'aria-label', 'Menu' );
		menu.setAttribute( 'aria-hidden', 'true' );

		var list = '';
		var pics = '';
		LINKS.forEach( function ( l, i ) {
			list += '<li style="--i:' + i + '"><a href="' + l.u + '" data-i="' + i + '"' + ( here( l.u ) ? ' aria-current="page"' : '' ) + '>' +
				'<span class="vv-menu-t">' + l.t + '</span><span class="vv-menu-s">' + l.s + '</span></a></li>';
			pics += '<img src="' + img + l.i + '" alt="" loading="lazy" decoding="async"' + ( i === 0 ? ' class="is-on"' : '' ) + '>';
		} );

		var logo = cfg.logo ? '<img src="' + cfg.logo + '" alt="Vivere">' : 'Vivere';
		menu.innerHTML =
			'<div class="vv-menu-bar"><a class="vv-menu-logo" href="/">' + logo + '<span>VIVERE</span></a>' +
			'<button type="button" class="vv-menu-close"><span></span><span></span><span class="screen-reader-text">Close menu</span></button></div>' +
			'<div class="vv-menu-body">' +
				'<nav class="vv-menu-nav" aria-label="Main"><ul>' + list + '</ul></nav>' +
				'<div class="vv-menu-pics" aria-hidden="true">' + pics + '</div>' +
			'</div>' +
			'<div class="vv-menu-foot">' +
				'<a class="vv-menu-cta" href="/spaces/#consultation">Book a consultation</a>' +
				'<a class="vv-menu-ghost" href="' + ( cfg.login || '/wp-login.php' ) + '">' + ( cfg.loggedIn ? 'My account' : 'Log in' ) + '</a>' +
				'<div class="vv-menu-social">' +
					'<a href="https://www.instagram.com/thevivereexperience" target="_blank" rel="noopener">Instagram</a>' +
					'<a href="https://www.linkedin.com/in/ann-uramu-52b8191a6" target="_blank" rel="noopener">LinkedIn</a>' +
					'<a href="https://www.youtube.com/@globalannuramu" target="_blank" rel="noopener">YouTube</a>' +
					'<a href="https://wa.me/2347079606881" target="_blank" rel="noopener">WhatsApp</a>' +
				'</div>' +
			'</div>';
		d.body.appendChild( menu );

		var close = menu.querySelector( '.vv-menu-close' );
		var imgs = menu.querySelectorAll( '.vv-menu-pics img' );
		var open = false;
		var lastFocus = null;

		function setOrigin() {
			var r = burger.getBoundingClientRect();
			var x = r.width ? r.left + r.width / 2 : window.innerWidth - 40;
			var y = r.height ? r.top + r.height / 2 : 40;
			menu.style.setProperty( '--ox', x + 'px' );
			menu.style.setProperty( '--oy', y + 'px' );
		}
		function toggle( state ) {
			if ( state === open ) { return; }
			open = state;
			if ( open ) {
				lastFocus = d.activeElement;
				setOrigin();
				menu.setAttribute( 'aria-hidden', 'false' );
				d.documentElement.classList.add( 'vv-menu-lock' );
				requestAnimationFrame( function () {
					menu.classList.add( 'is-open' );
					setTimeout( function () { close.focus( { preventScroll: true } ); }, reduce ? 0 : 350 );
				} );
			} else {
				menu.classList.remove( 'is-open' );
				menu.setAttribute( 'aria-hidden', 'true' );
				d.documentElement.classList.remove( 'vv-menu-lock' );
				if ( lastFocus && lastFocus.focus ) { lastFocus.focus( { preventScroll: true } ); }
			}
			burger.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
		}

		burger.addEventListener( 'click', function () { toggle( true ); } );
		close.addEventListener( 'click', function () { toggle( false ); } );
		d.addEventListener( 'keydown', function ( e ) {
			if ( ! open ) { return; }
			if ( e.key === 'Escape' ) { toggle( false ); return; }
			if ( e.key === 'Tab' ) {
				var f = menu.querySelectorAll( 'a, button' );
				var first = f[ 0 ], lastEl = f[ f.length - 1 ];
				if ( e.shiftKey && d.activeElement === first ) { e.preventDefault(); lastEl.focus(); }
				else if ( ! e.shiftKey && d.activeElement === lastEl ) { e.preventDefault(); first.focus(); }
			}
		} );
		menu.addEventListener( 'click', function ( e ) {
			var a = e.target.closest( 'a' );
			if ( a && a.getAttribute( 'href' ).indexOf( '#' ) > -1 && a.pathname === location.pathname ) { toggle( false ); }
		} );

		/* image follows the hovered link */
		function show( i ) {
			for ( var k = 0; k < imgs.length; k++ ) { imgs[ k ].classList.toggle( 'is-on', k === i ); }
		}
		menu.querySelectorAll( '.vv-menu-nav a' ).forEach( function ( a ) {
			var i = parseInt( a.getAttribute( 'data-i' ), 10 );
			a.addEventListener( 'mouseenter', function () { show( i ); } );
			a.addEventListener( 'focus', function () { show( i ); } );
		} );
		var cur = menu.querySelector( '[aria-current]' );
		if ( cur ) { show( parseInt( cur.getAttribute( 'data-i' ), 10 ) ); }

		/* gentle tilt on the picture */
		var pics = menu.querySelector( '.vv-menu-pics' );
		if ( ! reduce && window.matchMedia( '(hover: hover)' ).matches ) {
			menu.addEventListener( 'mousemove', function ( e ) {
				var x = e.clientX / window.innerWidth - 0.5;
				var y = e.clientY / window.innerHeight - 0.5;
				pics.style.transform = 'translate(' + ( x * 24 ).toFixed( 1 ) + 'px,' + ( y * 24 ).toFixed( 1 ) + 'px) rotate(' + ( x * 3 ).toFixed( 2 ) + 'deg)';
			} );
		}
	}

	function boot() { initIsland(); initMenu(); }
	if ( d.readyState === 'loading' ) { d.addEventListener( 'DOMContentLoaded', boot ); } else { boot(); }
}() );
