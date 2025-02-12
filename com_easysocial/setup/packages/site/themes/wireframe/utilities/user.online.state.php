<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2016 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

$message = $online ? 'COM_EASYSOCIAL_USER_CURRENTLY_ONLINE' : 'COM_EASYSOCIAL_USER_CURRENTLY_OFFLINE';
?>
<div class="es-user-status-indicator is-<?php echo $online ? 'online' : 'offline';?>"
	data-original-title="<?php echo $this->html('string.escape', JText::_($message));?>" 
	data-placement="top" 
	data-es-provide="tooltip"
>
</div>