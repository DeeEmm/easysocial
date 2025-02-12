EasySocial
.require()
.script('site/vendors/jquery.raty')
.done(function($){
	var ratings = $('[data-es-ratings-stars-<?php echo $reviews->id; ?>] ');
	ratings.raty({
		hints: [
				'<?php echo JText::_('APP_REVIEWS_RATING_BAD')?>',
				'<?php echo JText::_('APP_REVIEWS_RATING_POOR')?>',
				'<?php echo JText::_('APP_REVIEWS_RATING_REGULAR')?>',
				'<?php echo JText::_('APP_REVIEWS_RATING_GOOD')?>',
				'<?php echo JText::_('APP_REVIEWS_RATING_GORGEOUS')?>'
			],
		score: ratings.data('score'),
		readOnly: true
	});
});


