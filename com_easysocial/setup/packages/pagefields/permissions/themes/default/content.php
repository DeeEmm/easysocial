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
<div>
	<?php foreach ($checkboxForm as $item) { ?>
		<div class="o-checkbox">
			<input type="checkbox" id="stream-permissions-<?php echo $item->name; ?>" name="stream_permissions[]" value="<?php echo $item->name; ?>"<?php echo $item->selected ? ' checked="checked"' : ''; ?>/>
			<label for="stream-permissions-<?php echo $item->name; ?>"><?php echo JText::_('COM_EASYSOCIAL_APP_PAGE_PERMISSIONS_' . strtoupper($item->name) . 'S'); ?></label>
		</div>
	<?php } ?>
</div>
