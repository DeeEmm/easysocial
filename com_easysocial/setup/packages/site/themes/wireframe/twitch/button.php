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
<span data-twitch-login>
	<a href="javascript:void(0);" class="btn btn-es-twitch" data-twitch-login-button data-url="<?php echo $authorizeURL;?>">
		<i class="fab fa-twitch"></i>&nbsp; <?php echo JText::_($text);?>
	</a>
</span>
