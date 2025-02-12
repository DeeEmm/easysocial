EasySocial.require()
.done(function($) {

	if (window.twttr === undefined) {
		window.twttr = (function (d,s,id) {
			var t, js, fjs = d.getElementsByTagName(s)[0];

			if (d.getElementById(id)) {
				return;
			}

			js = d.createElement(s); js.id=id;
			js.src = "https://platform.twitter.com/widgets.js";
			fjs.parentNode.insertBefore(js, fjs);

			return window.twttr || (t = { _e: [], ready: function(f){ t._e.push(f) } });

		}(document, "script", "twitter-wjs"));
	}

	twttr.ready(function (twttr) {
		twttr.events.bind('loaded', function (event) {
			event.widgets.forEach(function (widget) {
				var sandboxRoot = $(widget.shadowRoot).children('.SandboxRoot');
				var embedded = sandboxRoot.children('.EmbeddedTweet');
				var embeddedTweet = sandboxRoot.find('.EmbeddedTweet-tweet');
				var callToActionMediaForward = sandboxRoot.find('.CallToAction--mediaForward');
				$(embedded).css({
					"border-style": "none",
					"max-width": "none",
					"border-radius": "3px"
				});
				$(embeddedTweet).css("border-style", "none");
				$(callToActionMediaForward).css({
					"border-left-style" : "none",
					"border-right-style" : "none",
					"border-bottom-style" : "none"
				});
			});
		});
	});
});
