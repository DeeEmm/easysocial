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
<li class="divider"></li>
<li data-broadcast-admin-delete data-id="<?php echo $streamItem->uid; ?>">
	<a href="javascript:void(0);"><?php echo JText::_('COM_ES_BROADCASTS_REMOVE_FROM_SITE');?></a>
</li>