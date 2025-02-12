<?php defined( '_JEXEC' ) or die( 'Unauthorized Access' ); ?>

EasySocial.require()
.script( 'admin/users/privacy' )
.done(function($){

	$( '[data-edit-privacy]' ).implement(
		'EasySocial.Controller.Profile.Privacy',
		{
			userId : "<?php echo $this->input->get('id', 0, 'int'); ?>"
		});
});
