<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<p class="help-block">
	<?php if ($includeNotePrefix) { ?>
	<b><?php echo JText::_('COM_EASYSOCIAL_NOTE');?>:</b>
	<?php } ?>
	<?php echo $text;?>
</p>
