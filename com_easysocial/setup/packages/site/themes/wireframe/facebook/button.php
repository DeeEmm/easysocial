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
	<a href="<?php echo $authorizeURL;?>" class="btn btn-es-facebook <?php echo $size;?>">
		<i class="fab fa-facebook"></i>&nbsp; <?php echo JText::_($text);?>
	</a>
</span>
