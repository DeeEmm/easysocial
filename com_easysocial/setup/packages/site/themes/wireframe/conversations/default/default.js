EasySocial
.require()
.script('site/conversations/conversations', 'site/vendors/lightbox')
.done(function($){

	$('[data-es-conversations]').implement(EasySocial.Controller.Conversations, {
		isMobile: <?php echo $this->isMobile() ? 'true' : 'false' ?>,
		attachments: <?php echo $this->config->get('conversations.attachments.enabled') ? 'true' : 'false' ?>,
		location: <?php echo $this->config->get('conversations.location') ? 'true' : 'false' ?>,
		extensionsAllowed : "<?php echo ES::makeString($this->config->get('conversations.attachments.types'), ',');?>",
		maxSize: "<?php echo $this->config->get('conversations.attachments.maxsize', 3 );?>mb",

		enterToSubmit: <?php echo $this->config->get('conversations.entersubmit') ? 'true' : 'false';?>,

		typingState: <?php echo $this->config->get('conversations.typing') ? 'true' : 'false'; ?>,
		typingMessage: "<?php echo JText::_('COM_ES_USER_IS_TYPING', true);?> <div class='es-typing-wave'><span></span><span></span><span></span></div>",
		userId: "<?php echo $this->my->id;?>",
		conversationId: "<?php echo $activeConversation ? $activeConversation->id : 'false';?>",
		userKey: "<?php echo md5($this->my->email . $this->jConfig->getValue('secret') . $this->my->password);?>",
		emoticons: '<?php echo $emoticons; ?>',
	});

	<?php if ($this->isMobile()) { ?>
	// for conversation in responsive view.
	$(document).on("togglees.conversation", function(event){
		$('[data-es-conversations]')
			.toggleClass("sidebar-open")
			.trigger("sidebarToggle");
	});
	<?php } ?>

	EasySocial.module('giphy/popbox', function($) {
		this.resolve(function(popbox) {

			var options = {
				'from': 'conversation'
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
