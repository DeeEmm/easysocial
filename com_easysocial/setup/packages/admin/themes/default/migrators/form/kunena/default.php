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
<div class="row">
	<div class="col-md-6">
		<div class="panel" data-start-widget>
			<?php echo $this->html('panel.heading', 'COM_EASYSOCIAL_MIGRATOR_KUNENA_READ_FIRST_TITLE', 'COM_EASYSOCIAL_MIGRATOR_KUNENA_INSTRUCTION'); ?>

			<div class="panel-body">
				<ul>
					<li>
						<?php echo JText::_('COM_EASYSOCIAL_MIGRATOR_KUNENA_NEW_TOPIC_STREAM');?>
					</li>
					<li>
						<?php echo JText::_('COM_EASYSOCIAL_MIGRATOR_KUNENA_NEW_REPLY_STREAM');?>
					</li>
					<li>
						<?php echo JText::_('COM_EASYSOCIAL_MIGRATOR_KUNENA_USER_AVATAR');?>
					</li>
				</ul>

				<p class="mt-20"><?php echo JText::_('COM_EASYSOCIAL_MIGRATOR_KUNENA_ENSURE'); ?></p>

				<ul class="mb-20">
					<li><?php echo JText::_('COM_EASYSOCIAL_MIGRATOR_KUNENA_UPGRADE_TO_LATEST');?></li>
					<li><?php echo JText::_('COM_EASYSOCIAL_MIGRATOR_BACKUP_EXISTING_DB');?></li>
					<li><?php echo JText::_('COM_EASYSOCIAL_MIGRATOR_SET_OFFLINE');?></li>
				</ul>

				<hr>
				<?php if ($installed) { ?>
						<a href="javascript:void(0);" class="btn btn-large btn-es-primary-o" data-start-migration><?php echo JText::_('COM_EASYSOCIAL_MIGRATOR_RUN_NOW');?></a>
				<?php } else { ?>
					<div class="text-error">
						<strong><i class="icon-es-delete mr-5"></i> <?php echo JText::_('COM_EASYSOCIAL_MIGRATOR_KUNENA_NOT_FOUND'); ?></strong>
					</div>
				<?php } ?>
				<hr>

				<div class="mt-20 small">
					<span class="label label-danger small"><?php echo JText::_('COM_EASYSOCIAL_FOOTPRINT_NOTE');?>:</span>
					<?php echo JText::_('COM_EASYSOCIAL_MIGRATOR_KUNENA_FOOTNOTE');?>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col-md-6">

		<div class="panel" data-migration-result style="display: none;">
			<?php echo $this->html('panel.heading', 'COM_EASYSOCIAL_MIGRATOR_RESULT'); ?>

			<div class="panel-body">
				<div class="es-progress-wrap t-hidden" data-progress>
					<div class="progress">
						<div style="width: 0%;" class="progress-bar progress-bar-info"></div>
					</div>
					<div class="progress-result" data-progress-result></div>
				</div>

				<a href="javascript:void(0);" class="viewLog btn btn-es-inverse btn-medium" style="display: none;">
					<?php echo JText::_('COM_EASYSOCIAL_VIEW_LOGS_BUTTON');?>
				</a>

				<ul class="scannedResult es-scanned-result list-unstyled">
					<li class="empty">
						<?php echo JText::_('COM_EASYSOCIAL_MIGRATOR_KUNENA_NO_ITEM'); ?>
					</li>
				</ul>
			</div>
		</div>

		<a href="<?php echo JRoute::_('index.php?option=com_easysocial&view=migrators&layout=kunena'); ?>" style="display: none;" data-kunena-back-button>
			<?php echo JText::_('COM_EASYSOCIAL_MIGRATOR_BACK_TO_KUNENA_PAGE');?>
		</a>
	</div>
</div>
