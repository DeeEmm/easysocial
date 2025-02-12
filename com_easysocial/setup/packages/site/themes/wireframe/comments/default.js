<?php if ($loadScripts) { ?>
EasySocial.require()
.script('site/comments/frame','site/vendors/lightbox')
.done(function($) {

	$('[data-es-comments]').addController('EasySocial.Controller.Comments', {
		'attachments': <?php echo $this->config->get('comments.attachments.enabled') ? 'true' : 'false';?>,
		'errorMessage': "<?php echo JText::_('COM_ES_COMMENT_ERROR_MESSAGE'); ?>",
		'emoticons': '<?php echo $emoticons; ?>',
		'currentUserId': '<?php echo $this->my->id; ?>'
	});

	lightbox.option({
	  'albumLabel': "<?php echo JText::_('COM_EASYSOCIAL_COMMENTS_ATTACHMENTS_PHOTOS_ITEM'); ?>"
	});

	EasySocial.module('giphy/popbox', function($) {
		this.resolve(function(popbox) {

			var options = {
				'from': 'comment'
			};

			return {
				content: EasySocial.ajax('site/views/giphy/getForm', options),
				id: "es",
				component: "",
				type: "giphy",
				exclusive: false,
				cache: true
			}
		});
	});

});
<?php } ?>
