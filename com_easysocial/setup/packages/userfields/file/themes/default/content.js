<?php
/**
* @package 		EasySocial
* @copyright	Copyright (C) 2010 - 2014 Stack Ideas Sdn Bhd. All rights reserved.
* @license 		Proprietary Use License http://stackideas.com/licensing.html
* @author 		Stack Ideas Sdn Bhd
*/
defined( '_JEXEC' ) or die( 'Unauthorized Access' );
?>
EasySocial
	.require()
	.script('apps/fields/user/file/content')
	.done(function($) {

		$('[data-field-<?php echo $field->id; ?>]').addController('EasySocial.Controller.Field.File', {
			required: <?php echo $field->required ? 1 : 0; ?>,
			id: <?php echo $field->id; ?>,
			inputName: "<?php echo $inputName; ?>"
		});
	});
