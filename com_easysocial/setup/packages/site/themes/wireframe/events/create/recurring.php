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
<div class="es-container">
	<div class="es-content">
		<?php echo $this->html('html.snackbar', 'COM_EASYSOCIAL_HEADING_CREATING_RECURRING_EVENTS'); ?>

		<div class="es-forms">
			<div class="es-forms__group">
				<div class="es-forms__content">
					<div data-events-create>
						<div class="progress">
							<div class="progress-bar progress-bar-info progress-bar-striped" style="width: 0%" data-progress-bar></div>
						</div>

						<form method="post" action="<?php echo JRoute::_('index.php'); ?>" data-form style="display: none;">
							<?php echo JHTML::_('form.token'); ?>
							<input type="hidden" name="Itemid" value="<?php echo $this->input->get('Itemid', 0, 'int');?>" />
							<input type="hidden" name="option" value="com_easysocial" />
							<input type="hidden" name="view" value="events" />
							<input type="hidden" name="layout" value="createRecurringSuccess" />
							<input type="hidden" name="id" value="<?php echo $event->id;?>" />
							<input type="hidden" name="fromManagePage" value="<?php echo isset($event->fromManagePage) && $event->fromManagePage ? $event->fromManagePage : false ;?>" />
						</form>
					</div>
				</div>
				<div class="es-forms__actions">
					<div class="o-form-actions">
					<?php echo JText::_('COM_EASYSOCIAL_HEADING_CREATING_RECURRING_EVENTS_WARNING'); ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

