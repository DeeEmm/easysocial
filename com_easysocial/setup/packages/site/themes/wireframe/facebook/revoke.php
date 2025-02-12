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
<a href="javascript:void(0);" class="btn btn-es-danger-o btn-sm" data-facebook-revoke data-callback="<?php echo $callback;?>">
	<i class="fab fa-facebook"></i>&nbsp; <?php echo JText::_( 'COM_EASYSOCIAL_OAUTH_REVOKE_ACCESS');?>
</a>
