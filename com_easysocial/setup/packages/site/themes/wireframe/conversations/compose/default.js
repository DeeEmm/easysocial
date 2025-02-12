
EasySocial
.require()
.script('site/conversations/composer')
.done(function($) {

	$.template("textboxlist/item", '<div class="textboxlist-item[%== (this.locked) ? " is-locked" : "" %]" data-textboxlist-item><span class="textboxlist-itemContent" data-textboxlist-itemContent>[%== html %]</span><div class="textboxlist-itemRemoveButton" data-textboxlist-itemRemoveButton><i class="fa fa-times"></i></a></div>');

	// Implement controller on the conversation compose form
	$('[data-conversations-composer]').implement(EasySocial.Controller.Conversations.Composer, {
		location: <?php echo $this->config->get( 'conversations.location' ) ? 'true' : 'false' ?>,
		attachments: <?php echo $this->config->get( 'conversations.attachments.enabled' ) ? 'true' : 'false' ?>,
		extensionsAllowed: "<?php echo ES::makeString( $this->config->get( 'conversations.attachments.types' ) , ',' );?>",
		maxSize: "<?php echo $this->config->get( 'conversations.attachments.maxsize' , 3 );?>mb",
		showNonFriend: <?php echo $this->config->get( 'conversations.nonfriend' ) || !$this->config->get( 'friends.enabled' ) ? 1 : 0; ?>,
		emptyMessage: '<?php echo JText::_('COM_ES_CONVERSATION_NO_USERS_FOUND_MESSAGE'); ?>',
		isNewPage: true,
		emoticons: '<?php echo $emoticons; ?>'
	});
});
