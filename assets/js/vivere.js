/* Vivere front end: hero slider built on top of native blocks, join form message, Ask Vivere. */
(function () {
	'use strict';

	var ICON = {
		prev: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 6l-6 6 6 6"/></svg>',
		next: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6"/></svg>',
		pause: '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><rect x="7" y="6" width="3.2" height="12" rx="1"/><rect x="13.8" y="6" width="3.2" height="12" rx="1"/></svg>',
		play: '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M8 5.5v13l11-6.5z"/></svg>'
	};

	function esc(s) {
		return String(s).replace(/[&<>"]/g, function (c) {
			return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c];
		});
	}

	function initHero(hero) {
		var slides = Array.prototype.slice.call(hero.querySelectorAll(':scope > .vv-slide'));
		var n = slides.length;
		if (n < 2) { if (n) { slides[0].classList.add('is-active'); } return; }

		var data = slides.map(function (s) {
			var img = s.querySelector('.vv-slide-img img');
			var label = s.querySelector('.vv-label');
			var title = s.querySelector('.vv-hero-title');
			return {
				src: img ? (img.currentSrc || img.src) : '',
				label: label ? label.textContent.trim() : '',
				title: title ? title.textContent.trim() : ''
			};
		});

		var cur = 0, busy = false, paused = false, raf = 0, start = 0, DUR = 7000;
		var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

		hero.setAttribute('aria-roledescription', 'carousel');
		if (!hero.getAttribute('aria-label')) { hero.setAttribute('aria-label', 'Vivere highlights'); }
		slides.forEach(function (s, i) {
			s.setAttribute('role', 'group');
			s.setAttribute('aria-roledescription', 'slide');
			s.setAttribute('aria-label', (i + 1) + ' of ' + n);
		});

		var cards = document.createElement('div');
		cards.className = 'vv-hero-cards';
		var controls = document.createElement('div');
		controls.className = 'vv-hero-controls';
		controls.innerHTML =
			'<button type="button" class="vv-ctrl" data-act="prev" aria-label="Previous slide">' + ICON.prev + '</button>' +
			'<button type="button" class="vv-ctrl" data-act="next" aria-label="Next slide">' + ICON.next + '</button>' +
			'<button type="button" class="vv-ctrl" data-act="pause" aria-label="Pause slideshow">' + ICON.pause + '</button>' +
			'<div class="vv-progress" aria-hidden="true"><i></i></div>' +
			'<div class="vv-counter" aria-hidden="true">01</div>';
		hero.appendChild(cards);
		hero.appendChild(controls);
		var bar = controls.querySelector('.vv-progress i');
		var counter = controls.querySelector('.vv-counter');
		var pauseBtn = controls.querySelector('[data-act="pause"]');

		function renderCards(animate) {
			var html = '';
			for (var k = 1; k < n; k++) {
				var i = (cur + k) % n, d = data[i];
				html += '<button type="button" class="vv-hcard' + (animate ? ' is-entering' : '') + '" style="animation-delay:' + (k * 60) + 'ms" data-i="' + i + '" aria-label="Show slide: ' + esc(d.title) + '">' +
					(d.src ? '<img src="' + esc(d.src) + '" alt="" loading="lazy">' : '') +
					'<span class="vv-hcard-txt"><small>' + esc(d.label) + '</small><b>' + esc(d.title) + '</b></span></button>';
			}
			cards.innerHTML = html;
		}

		function show(i, animate) {
			slides.forEach(function (s, j) {
				s.classList.toggle('is-active', j === i);
				s.classList.remove('is-out');
				if (j === i) { s.removeAttribute('aria-hidden'); } else { s.setAttribute('aria-hidden', 'true'); }
				s.inert = j !== i;
			});
			cur = i;
			counter.textContent = String(i + 1).padStart(2, '0');
			renderCards(animate);
		}

		function go(to, fromCard) {
			if (busy || to === cur) { return; }
			busy = true;
			var old = slides[cur];
			old.classList.add('is-out');
			var card = fromCard || cards.querySelector('.vv-hcard[data-i="' + to + '"]');

			function done() {
				var target = slides[to];
				target.classList.add('is-out');
				show(to, true);
				target.classList.add('is-out');
				requestAnimationFrame(function () {
					requestAnimationFrame(function () { target.classList.remove('is-out'); });
				});
				busy = false;
				restart();
			}

			if (reduce || !card || !card.offsetParent || !data[to].src || !card.animate) {
				setTimeout(done, 250);
				return;
			}

			var hr = hero.getBoundingClientRect(), cr = card.getBoundingClientRect();
			var g = document.createElement('div');
			g.className = 'vv-hero-grow';
			g.innerHTML = '<img src="' + esc(data[to].src) + '" alt="">';
			var from = { top: (cr.top - hr.top) + 'px', left: (cr.left - hr.left) + 'px', width: cr.width + 'px', height: cr.height + 'px', borderRadius: '16px' };
			Object.keys(from).forEach(function (k) { g.style[k] = from[k]; });
			hero.appendChild(g);
			card.style.opacity = '0';
			var anim = g.animate(
				[from, { top: '0px', left: '0px', width: hr.width + 'px', height: hr.height + 'px', borderRadius: '0px' }],
				{ duration: 950, easing: 'cubic-bezier(.76,0,.24,1)', fill: 'forwards' }
			);
			anim.onfinish = function () { done(); requestAnimationFrame(function () { g.remove(); }); };
		}

		function next() { go((cur + 1) % n); }
		function prev() { go((cur - 1 + n) % n); }

		function tick(t) {
			if (!start) { start = t; }
			var p = Math.min(1, (t - start) / DUR);
			bar.style.width = (p * 100) + '%';
			if (p >= 1) { start = 0; next(); return; }
			raf = requestAnimationFrame(tick);
		}
		function restart() {
			cancelAnimationFrame(raf);
			start = 0;
			bar.style.width = '0%';
			if (!paused && !reduce && !document.hidden) { raf = requestAnimationFrame(tick); }
		}
		function setPaused(v) {
			paused = v;
			pauseBtn.setAttribute('aria-label', v ? 'Play slideshow' : 'Pause slideshow');
			pauseBtn.innerHTML = v ? ICON.play : ICON.pause;
			if (v) { cancelAnimationFrame(raf); bar.style.width = '0%'; } else { restart(); }
		}

		controls.addEventListener('click', function (e) {
			var b = e.target.closest('[data-act]');
			if (!b) { return; }
			var a = b.getAttribute('data-act');
			if (a === 'next') { next(); } else if (a === 'prev') { prev(); } else { setPaused(!paused); }
		});
		cards.addEventListener('click', function (e) {
			var c = e.target.closest('.vv-hcard');
			if (c) { go(+c.getAttribute('data-i'), c); }
		});
		hero.addEventListener('keydown', function (e) {
			if (e.key === 'ArrowRight') { next(); }
			if (e.key === 'ArrowLeft') { prev(); }
		});
		document.addEventListener('visibilitychange', function () { if (!paused) { restart(); } });

		if (reduce) { setPaused(true); }
		show(0, false);
		hero.classList.add('is-ready');
		restart();
	}

	function initJoin() {
		var form = document.querySelector('.vv-join-form');
		if (!form) { return; }
		var msg = form.querySelector('.vv-fine');
		var state = new URLSearchParams(window.location.search).get('joined');
		if (msg && state === '1') { msg.textContent = 'Thank you. You are on the list.'; }
		if (msg && state === 'invalid') { msg.textContent = 'Please enter a valid email address.'; }
	}

	function initForms() {
		var state = new URLSearchParams(window.location.search).get('sent');
		if (!state) { return; }
		document.querySelectorAll('.vv-form').forEach(function (f) {
			var m = f.querySelector('.vv-form-msg');
			if (m) { m.textContent = state === '1' ? 'Thank you. We will be in touch within 48 hours.' : 'Please check your email address and try again.'; }
		});
		var first = document.querySelector('.vv-form');
		if (first && !window.location.hash) { first.scrollIntoView({ block: 'center' }); }
	}

	function initAssist() {
		var b = document.querySelector('.vv-assist');
		if (!b) { return; }
		b.addEventListener('click', function () {
			window.location.href = '/contact/?ask=1';
		});
	}

	function boot() {
		document.querySelectorAll('.vv-hero').forEach(initHero);
		initJoin();
		initForms();
		initAssist();
	}
	if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', boot); } else { boot(); }
})();
