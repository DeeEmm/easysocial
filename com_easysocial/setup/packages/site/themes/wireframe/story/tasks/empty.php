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
<div class="es-story-tasks-form">
	<div class="es-story-tasks-textbox">
		<div class="o-alert o-alert--info">
			<?php echo JText::sprintf('APP_TASKS_CREATE_MILESTONE_FIRST', $cluster->cluster_type); ?>
			<button type="button" class="o-alert__close" data-dismiss="alert"><span aria-hidden="true">×</span></button>
		</div>
        <?php if ($cluster->canCreateMilestones()) { ?>
		<a href="<?php echo $permalink;?>" class="btn btn-es-default-o"><?php echo JText::_('APP_GROUP_TASKS_CREATE_FIRST_MILESTONE'); ?></a>
        <?php } ?>
	</div>
</div>
