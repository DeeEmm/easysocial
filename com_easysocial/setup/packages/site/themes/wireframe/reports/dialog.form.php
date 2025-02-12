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
<dialog>
	<width>600</width>
	<height>350</height>
	<title><?php echo $title; ?></title>
	<content>
		<?php if ($description) { ?>
		<p class="t-fs--sm"><?php echo $description; ?></p>
		<?php } ?>

		<?php if ($this->my->id && $this->access->exceeded('reports.limit', 1)) { ?>
		<p class="t-fs--sm"><?php echo JText::_('COM_EASYSOCIAL_REPORTS_LIMIT_EXCEEDED'); ?></p>
		<?php } else { ?>
			<textarea data-reports-message class="o-form-control t-lg-mt--md" style="width: 100%;height: 100px;" placeholder="<?php echo JText::_('COM_EASYSOCIAL_REPORTS_SUBMIT_REPORT_PLACEHOLDER'); ?>"></textarea>

			<div class="t-fs--sm t-lg-mt--md">
				<?php echo JText::_( 'COM_EASYSOCIAL_REPORTS_SUBMIT_REPORT_FOOTNOTE' );?>
			</div>
		<?php } ?>
	</content>
	<buttons>
		<button data-cancel-button type="button" class="btn btn-es-default btn-sm"><?php echo JText::_('COM_ES_CANCEL'); ?></button>

		<?php if ($this->my->guest || !$this->access->exceeded('reports.limit', 1)) { ?>
		<button data-report-button type="button" class="btn btn-es-primary btn-sm"><?php echo JText::_('COM_EASYSOCIAL_REPORT_BUTTON'); ?></button>
		<?php } ?>
	</buttons>
</dialog>
