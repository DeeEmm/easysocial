<?php
/**
* @package 		EasySocial
* @copyright	Copyright (C) 2010 - 2014 Stack Ideas Sdn Bhd. All rights reserved.
* @license 		Proprietary Use License http://stackideas.com/licensing.html
* @author 		Stack Ideas Sdn Bhd
*/
defined( '_JEXEC' ) or die( 'Unauthorized Access' );
?>
EasySocial.require().script('apps/fields/user/avatar/content').done(function($) {
	$('[data-field-<?php echo $field->id; ?>]').addController('EasySocial.Controller.Field.Avatar', {
		required: <?php echo $field->required ? 1 : 0; ?>,
		id: <?php echo $field->id; ?>,
		group: '<?php echo $group; ?>',
		categoryId: '<?php echo $categoryId; ?>',
		hasAvatar: <?php echo $hasAvatar ? 'true' : 'false'; ?>,
		defaultAvatar: '<?php echo $systemAvatar; ?>',
		origSource: '<?php echo $imageSource ? $imageSource : $systemAvatar;?>',
		newRegistration: <?php echo $newRegistration ? 'true' : 'false'; ?>
	});
});
