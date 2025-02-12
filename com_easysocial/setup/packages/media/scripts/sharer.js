!function() {
	var es = {
		"counter" : 1,
		"load": function() {
			var version = '1';
			var script = document.querySelector('#es-sharer-js');
			var frame = script.getAttribute('data-frame');

			// Get links that we need to replace
			var links = document.querySelectorAll('[data-es-sharer]');

			for (var i = 0; i < links.length; i++) {
				link = links[i];

				var target = link.getAttribute('data-link') ? decodeURIComponent(link.getAttribute('data-link')) : document.location.href;
				var id = 'es-button-' + i;
				var language;

				if (window.navigator.languages) {
					language = window.navigator.languages[0];
				} else {
					language = window.navigator.userLanguage || window.navigator.language;
				}

				src = frame + '&id=' + id + '&link=' + encodeURIComponent(target) + '&source=' + btoa(window.location.href) + '&lang=' + language;

				var div = document.createElement('div');
				div.className = 'es-sharer-btn';
				div.innerHTML = '<iframe id="' + id + '" data-es-sharer-iframe frameBorder="0" allowTransparency="true" scrolling="no" width="1" height="1" src="'+src+'"></iframe>';
				link.parentNode.replaceChild(div, link);
			}
		},

		"resize": function(event) {
			var data = event.data;

			if (!data.id) {
				return;
			}

			var iframe = document.getElementById(data.id);

			if (iframe === null || iframe === undefined) {
				return;
			}

			iframe.width = data.width;
			iframe.height = data.height;
		},

		"check": function() {
			if (document.readyState === "complete" || document.readyState === "interactive") {
				if (es.to) {
					clearTimeout(es.to);
				}
				es.load();
			} else {
				var wait = es.counter * 10;

				if (wait > 100) {
					wait = 100;
				}

				es.to = setTimeout(function(){
					es.check()
				}, wait);

				es.counter++;
			}
		}
	};

	// Handle message event to avoid cross browser error notices
	if (window.addEventListener) {
		window.addEventListener("message", es.resize, false);
	} else {
		window.attachEvent("onmessage", es.resize);
	}

	es.check();
}();
