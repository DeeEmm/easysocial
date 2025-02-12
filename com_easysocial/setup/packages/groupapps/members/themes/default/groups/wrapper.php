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
<div class="es-app-group-members" data-wrapper>
	<?php if ($admins) { ?>
		<h3><?php echo JText::_('APP_GROUP_MEMBERS_FILTER_ADMINS'); ?></h3>
		<?php echo $this->includeTemplate('apps/group/members/groups/list', array('users' => $admins, 'pagination' => false)); ?>
	<?php } ?>

	<?php if ($admins) { ?>
	<br />

	<h3><?php echo JText::_('APP_GROUP_MEMBERS_FILTER_MEMBERS'); ?></h3>
	<?php } ?>

	<?php echo $this->includeTemplate('apps/group/members/groups/list', array('users' => $users, 'pagination' => $pagination)); ?>
</div>
