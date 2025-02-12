<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div class="es-stream-hide-notice" data-hidden-notice data-type="<?php echo $type; ?>">
	<div class="o-row">
		<div class="o-col--8">
			<?php echo JText::_('COM_EASYSOCIAL_STREAM_ITEM_HIDDEN_SUCCESS'); ?>
		</div>
		<div class="o-col--2 t-text-center">
			<a href="javascript:void(0);" class="btn btn-es-default-o btn-sm" data-unhide><?php echo JText::_('COM_EASYSOCIAL_STREAM_UNDO'); ?></a>
		</div>
	</div>
</div>