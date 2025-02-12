<?php
/**
* @package 		EasySocial
* @copyright	Copyright (C) 2010 - 2014 Stack Ideas Sdn Bhd. All rights reserved.
* @license 		Proprietary Use License http://stackideas.com/licensing.html
* @author 		Stack Ideas Sdn Bhd
*/
defined( '_JEXEC' ) or die( 'Unauthorized Access' );
?>
EasySocial.module('field.country/<?php echo $field->id; ?>', function($) {
	this.resolve(<?php echo ES::json()->encode( $countries ); ?>);
});

EasySocial.require().script('apps/fields/user/country/content').done(function($) {
	$('[data-field-<?php echo $field->id; ?>]').addController('EasySocial.Controller.Field.Country', {
		required: <?php echo $field->required ? 1 : 0; ?>,
		fieldname: '<?php echo $inputName; ?>',
		id: <?php echo $field->id; ?>
	});
});
