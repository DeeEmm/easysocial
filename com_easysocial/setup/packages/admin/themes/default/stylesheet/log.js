
EasySocial.ready(function($) {

try {

	<?php if ($errors) { ?>
		console.info('There was an error building the stylesheets. View log below.');	
		

		<?php foreach ($errors as $error) { ?>
		console.groupCollapsed('<?php echo addslashes($error->message);?>');
			<?php foreach ($error->details as $detail) { ?>
				<?php if ($detail->type == 'error') { ?>
					console.log('<?php echo addslashes($detail->message);?>');
				<?php } ?>
			<?php } ?>
		console.groupEnd();
		<?php } ?>
	<?php } ?>

} catch(e) {};


});
