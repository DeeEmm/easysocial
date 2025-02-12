EasySocial
.require()
.script('site/conversations/createMassConversation')
.done(function($) {
	$('[data-conversation-create]').addController('EasySocial.Controller.Conversations.CreateMassConversation', {
		message: <?php echo json_encode($message); ?>,
		uploadId: <?php echo json_encode($uploadId); ?>,
		totalUsers: <?php echo $totalUsers; ?>
	});
});
