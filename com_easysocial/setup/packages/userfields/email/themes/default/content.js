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
	.script('apps/fields/user/email/content')
	.done(function($) {
		$('[data-field-<?php echo $field->id; ?>]').addController('EasySocial.Controller.Field.Email', {
			required: <?php echo $field->required ? 1 : 0; ?>,
			regex: <?php echo $params->get('regex_validate', false) ? 1 : 0; ?>,
			regexFormat: '<?php echo addslashes($params->get('regex_format', '')); ?>',
			regexModifier: '<?php echo $params->get('regex_modifier', ''); ?>'
		});
	});
