<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<?php if ($params->get('use_maps')) { ?>
EasySocial.require().script('apps/fields/user/address/display_content').done(function($) {
	$('[data-field="<?php echo $field->id; ?>"]').addController('EasySocial.Controller.Field.Address.Display', {
		staticMap: <?php echo (int) $params->get('static_maps', 0); ?>,
		mapElementId: 'addressfield-map-<?php echo $field->id; ?>',
		zoom: <?php echo (int) $params->get('zoom_maps', 15); ?>
	});
});
<?php }
