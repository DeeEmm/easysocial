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
<span data-oauth-login>
	<a href="#" class="btn btn-es-default-o btn-es-google <?php echo $size;?>"
		data-oauth-login-button
		data-url="<?php echo $url;?>"
		data-popup="<?php echo ES::isJoomla4() ? '0' : '1';?>"
	>
		<img src="<?php echo rtrim(JURI::root(), '/');?>/media/com_easysocial/images/logo-google.svg" alt="" width="16" height="16">&nbsp; <?php echo JText::_($text);?>
	</a>
</span>
