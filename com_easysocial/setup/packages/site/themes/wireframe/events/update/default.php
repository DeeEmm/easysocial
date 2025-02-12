<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2014 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div data-events-update>
	<div class="view-heading mb-20">
		<h3><?php echo JText::_('COM_EASYSOCIAL_HEADING_UPDATE_EVENTS_APPLYING_CHANGES'); ?></h3>
		<p><?php echo JText::_('COM_EASYSOCIAL_HEADING_UPDATE_EVENTS_APPLYING_CHANGES_DESC'); ?></p>
	</div>

	<div class="progress">
		<div class="progress-bar progress-bar-info progress-bar-striped" style="width: 0%" data-progress-bar></div>
	</div>

	<div>
		<?php echo JText::_('COM_EASYSOCIAL_HEADING_UPDATE_EVENTS_APPLYING_CHANGES_WARNING'); ?>
	</div>

	<form method="post" action="<?php echo JRoute::_('index.php'); ?>" data-form style="display: none;">
		<?php echo JHTML::_('form.token'); ?>
		<input type="hidden" name="Itemid" value="<?php echo $this->input->get('Itemid', 0, 'int');?>" />
		<input type="hidden" name="option" value="com_easysocial" />
		<input type="hidden" name="view" value="events" />
		<input type="hidden" name="layout" value="updateRecurringSuccess" />
		<input type="hidden" name="id" value="<?php echo $event->id;?>" />
	</form>
</div>
