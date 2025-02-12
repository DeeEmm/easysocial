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
	<div class="es-content sharer-embed">
		<?php echo $this->html('html.snackbar', 'COM_ES_SHARE_BUTTON', 'div'); ?>

		<p><?php echo JText::_('COM_ES_SHARE_BUTTON_INFO');?></p>

		<div class="o-form-group">
			<textarea class="o-form-control" style="min-height: 200px;" data-es-embed-textarea><?php echo $this->output('site/sharer/example', array('affiliationId' => $affiliationId));?></textarea>
		</div>

		<div class="o-form-group">
			<div class="gdpr-download-link t-text--center t-lg-mt--xl">
				<a href="javascript:void(0);" class="btn btn-es-primary" data-es-copy>
					<i class="far fa-clipboard"></i>&nbsp; <?php echo JText::_('COM_ES_COPY_TO_CLIPBOARD');?>
				</a>
			</div>
		</div>
	</div>
</div>
