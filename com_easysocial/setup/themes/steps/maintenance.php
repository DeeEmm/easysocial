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
<form name="installation" data-installation-form>

	<div class="mt-5" data-users-progress>
		<div class="mb-3 d-none" data-progress-complete-message>
			<?php echo JText::_('Users synchronization completed');?>
		</div>

		<div class="mb-3" data-progress-active-message>
			<?php echo JText::_('Synchronizing users...');?>
		</div>

		<div class="si-progress mb-3">
			<div class="si-progress__bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" data-progress-bar style="width: 0%;"></div>
		</div>
	</div>

	<div class="mt-5" data-profiles-progress>
		<div class="mb-3 d-none" data-progress-complete-message>
			<?php echo JText::_('Profile types synchronization completed');?>
		</div>

		<div class="mb-3" data-progress-active-message>
			<?php echo JText::_('Synchronizing users that does not belong to a profile...');?>
		</div>

		<div class="si-progress mb-3">
			<div class="si-progress__bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" data-progress-bar style="width: 0%;"></div>
		</div>
	</div>

	<div class="mt-5" data-sync-progress>
		<div class="mb-3 d-none" data-completed-message>
			<?php echo JText::_('Execution of maintenance scripts completed');?>
		</div>

		<div class="mb-3" data-active-message>
			<?php echo JText::_('Running Maintenance Scripts');?>
		</div>

		<div class="si-progress mb-3">
			<div class="si-progress__bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" data-progress-bar style="width: 0%;"></div>
		</div>

		<div class="si-card si-container-overflow mb-4 p-3" style="height: 20vh">
			<ol class="si-install-logs" data-script-logs style="font-size: 14px;">
			</ol>
		</div>
	</div>


	<input type="hidden" name="option" value="com_easysocial" />
	<input type="hidden" name="active" value="complete" />
</form>


<script>
$(document).ready(function() {
	es.maintenance.init();
});
</script>
