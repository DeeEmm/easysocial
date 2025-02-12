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
<div data-recurring-events>
	<div class="progress">
		<div class="progress-bar progress-bar-info progress-bar-striped" style="width: 0%" data-progress-bar></div>
	</div>

	<form method="post" action="<?php echo JRoute::_('index.php'); ?>" data-form style="display: none;">
		<?php echo JHTML::_('form.token'); ?>
		<input type="hidden" name="option" value="com_easysocial" />
		<input type="hidden" name="view" value="events" />
		<input type="hidden" name="layout" value="approveRecurringSuccess" />
		<input type="hidden" name="ids" value="<?php echo $this->html('string.escape', $eventids); ?>" />
	</form>
</div>
