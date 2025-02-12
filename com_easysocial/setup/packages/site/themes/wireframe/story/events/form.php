<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div class="es-story-event-form" data-story-event-base
	data-error-empty="<?php echo JText::_('COM_EASYSOCIAL_STORY_EVENT_INSUFFICIENT_DATA', true);?>"
	data-error-datetime="<?php echo JText::_('COM_EASYSOCIAL_STORY_EVENT_INVALID_START_END_DATETIME', true);?>"
>
	<div class="o-form-group ">
		<select data-story-event-category class="o-form-control">
			<option value=""><?php echo JText::_('COM_ES_CLUSTERS_SELECT_CATEGORY'); ?></option>
		<?php foreach ($categories as $category) { ?>

			<option value="<?php echo $category->id; ?>" <?php echo $category->container ? 'disabled="disabled"' : ''; ?>><?php echo str_repeat('|&mdash;', $category->getDepth()); ?> <?php echo $category->_('title'); ?></option>
		<?php } ?>
		</select>
	</div>

	<div data-story-event-form style="display: none;" class="t-lg-mt--md"></div>

	<?php echo $this->html('html.loading'); ?>
</div>
