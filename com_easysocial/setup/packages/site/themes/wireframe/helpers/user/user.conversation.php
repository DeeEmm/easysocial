<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<?php if ($this->my->canStartConversation($user->id)) { ?>
<button type="button" class="btn btn-es-default-o btn-<?php echo $buttonSize;?> btn--es-conversations-compose"
	<?php if ($useConverseKit) { ?>
	data-ck-chat="<?php echo $user->id;?>"
	<?php } else { ?>
	data-es-conversations-compose
	data-id="<?php echo $user->id;?>"
	<?php } ?>
	data-es-provide="tooltip"
	data-original-title="<?php echo JText::_('COM_EASYSOCIAL_PROFILE_SEND_MESSAGE', true); ?>">
	<i class="fa fa-envelope"></i>
</button>
<?php } ?>
