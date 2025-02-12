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
		<div class="es-story-tasks-list t-lg-mb--lg" data-story-tasks-list>
			<div class="es-story-tasks-form" data-story-tasks-form>
				<input type="text" class="o-form-control" data-story-tasks-input placeholder="<?php echo JText::_('APP_GROUP_TASKS_STORY_TITLE_PLACEHOLDER', true );?>" />
			</div>
		</div>

		<div class="o-grid o-grid--gutters">
			<div class="o-grid__cell">
				<select class="o-form-control" data-story-tasks-milestone>
					<?php foreach ($milestones as $milestone) { ?>
					<option value="<?php echo $milestone->id;?>"><?php echo $milestone->title;?></option>
					<?php } ?>
				</select>
			</div>

			<div class="o-grid__cell o-grid__cell--auto-size">
				<?php echo $this->html('form.calendar', 'due', '', 'due', array('data-story-tasks-due')); ?>
			</div>
		</div>
	</div>
</div>
