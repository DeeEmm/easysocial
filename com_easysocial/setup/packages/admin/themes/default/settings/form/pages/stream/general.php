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
		<div class="panel">
			<?php echo $this->html('panel.heading', 'COM_EASYSOCIAL_GENERAL_SETTINGS_FEATURES'); ?>

			<div class="panel-body">

				<?php echo $this->html('settings.toggle', 'stream.quickposting.enabled', 'COM_ES_QUICKPOSTING_STREAM'); ?>
				<?php echo $this->html('settings.toggle', 'stream.bookmarks.enabled', 'COM_EASYSOCIAL_STREAM_SETTINGS_ALLOW_BOOKMARKS'); ?>
				<?php echo $this->html('settings.toggle', 'stream.pin.enabled', 'COM_EASYSOCIAL_STREAM_SETTINGS_PIN_ENABLE'); ?>
				<?php echo $this->html('settings.toggle', 'stream.scheduled.enabled', 'COM_ES_HEADING_STREAM_SCHEDULED', '', 'data-scheduled-enable'); ?>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_ES_HEADING_STREAM_SCHEDULED_TIMEFORMAT'); ?>

					<div class="col-md-7">
						<?php echo $this->html('grid.selectlist', 'stream.scheduled.timeformat', $this->config->get('stream.scheduled.timeformat', '24'), array(
								array('value' => '12', 'text' => 'COM_ES_SETTINGS_DISPLAY_TIME_FORMAT_12H'),
								array('value' => '24', 'text' => 'COM_ES_SETTINGS_DISPLAY_TIME_FORMAT_24H')
							)); ?>
					</div>
				</div>

				<?php echo $this->html('settings.toggle', 'stream.groups.public', 'COM_ES_STREAM_SETTINGS_INCLUDE_PUBLIC_GROUPS'); ?>
				<?php echo $this->html('settings.toggle', 'stream.clusters.private', 'COM_EASYSOCIAL_STREAM_SETTINGS_INCLUDE_PRIVATE_CLUSTERS'); ?>
				<?php echo $this->html('settings.toggle', 'stream.exclude.admin', 'COM_EASYSOCIAL_STREAM_SETTINGS_EXCLUDE_SITE_ADMIN'); ?>
				<?php echo $this->html('settings.toggle', 'stream.archive.enabled', 'COM_EASYSOCIAL_STREAM_SETTINGS_ARCHIVE_ENABLE', '', 'data-archive-enable'); ?>

				<div class="form-group <?php echo $this->config->get('stream.archive.enabled') ? '' : 't-hidden';?>" data-archive-stream-setting>
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_STREAM_SETTINGS_ARCHIVE_DURATION'); ?>

					<div class="col-md-7">
						<?php echo $this->html('grid.selectlist', 'stream.archive.duration', $this->config->get('stream.archive.duration'), array(
								array('value' => '3', 'text' => 'COM_EASYSOCIAL_STREAM_SETTINGS_3_MONTHS'),
								array('value' => '6', 'text' => 'COM_EASYSOCIAL_STREAM_SETTINGS_6_MONTHS'),
								array('value' => '12', 'text' => 'COM_EASYSOCIAL_STREAM_SETTINGS_12_MONTHS'),
								array('value' => '18', 'text' => 'COM_EASYSOCIAL_STREAM_SETTINGS_18_MONTHS'),
								array('value' => '24', 'text' => 'COM_EASYSOCIAL_STREAM_SETTINGS_24_MONTHS')
							)); ?>
					</div>
				</div>

				<div class="form-group <?php echo $this->config->get('stream.archive.enabled') ? '' : 't-hidden';?>" data-archive-stream-setting>
					<?php echo $this->html('panel.label', 'COM_ES_STREAM_SETTINGS_ARCHIVE_LIMIT'); ?>

					<div class="col-md-7">
						<?php echo $this->html('grid.selectlist', 'stream.archive.limit', $this->config->get('stream.archive.limit'), array(
								array('value' => '50', 'text' => 'COM_ES_STREAM_SETTINGS_ARCHIVE_50_ITEMS'),
								array('value' => '100', 'text' => 'COM_ES_STREAM_SETTINGS_ARCHIVE_100_ITEMS'),
								array('value' => '250', 'text' => 'COM_ES_STREAM_SETTINGS_ARCHIVE_250_ITEMS'),
								array('value' => '500', 'text' => 'COM_ES_STREAM_SETTINGS_ARCHIVE_500_ITEMS'),
							)); ?>
					</div>
				</div>

				<?php echo $this->html('settings.toggle', 'stream.pushtop.reactions', 'COM_ES_STREAM_SETTINGS_PUSHTOP_REACTIONS'); ?>
				<?php echo $this->html('settings.toggle', 'stream.copylink.enabled', 'COM_ES_STREAM_SETTING_COPY_LINK'); ?>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_ES_STREAM_SETTING_FILTER_HASHTAGS'); ?>

					<div class="col-md-7">
						<?php echo $this->html('grid.selectlist', 'stream.filter.hashtag', $this->config->get('stream.filter.hashtag'), array(
								array('value' => 'or', 'text' => 'COM_ES_STREAM_SETTING_FILTER_HASHTAGS_TYPE_OR'),
								array('value' => 'and', 'text' => 'COM_ES_STREAM_SETTING_FILTER_HASHTAGS_TYPE_AND')
							)); ?>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="col-md-6">
		<div class="panel">
			<?php echo $this->html('panel.heading', 'COM_EASYSOCIAL_STREAM_SETTINGS_STREAM_PAGINATION'); ?>

			<div class="panel-body">
				<?php echo $this->html('settings.toggle', 'stream.pagination.autoload', 'COM_EASYSOCIAL_STREAM_SETTINGS_AUTO_LOAD_WHEN_SCROLL'); ?>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_ES_STREAM_SETTINGS_ORDERING'); ?>

					<div class="col-md-7">
						<?php echo $this->html('grid.selectlist', 'stream.pagination.ordering', $this->config->get('stream.pagination.ordering'), array(
							array('value' => 'modified', 'text' => JText::_('COM_ES_STREAM_ORDERING_MODIFIED')),
							array('value' => 'created', 'text' => JText::_('COM_ES_STREAM_ORDERING_CREATED'))
						)); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_STREAM_SETTINGS_DATA_FETCH_LIMIT'); ?>

					<div class="col-md-7">
						<?php echo $this->html('form.pagination', 'stream.pagination.pagelimit'); ?>
					</div>
				</div>
			</div>
		</div>

		<div class="panel">
			<?php echo $this->html('panel.heading', 'COM_EASYSOCIAL_STREAM_SETTINGS_TRANSLATIONS', '', '/administrators/configuration/stream-translation'); ?>

			<div class="panel-body">

				<?php echo $this->html('settings.toggle', 'stream.translations.azure', 'COM_EASYSOCIAL_STREAM_SETTINGS_ENABLE_AZURE_TRANSLATIONS'); ?>
				<?php echo $this->html('settings.textbox', 'stream.translations.azurekey', 'COM_EASYSOCIAL_STREAM_SETTINGS_AZURE_KEY'); ?>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_ES_STREAM_SETTINGS_AZURE_LOCATION'); ?>

					<div class="col-md-7">
						<?php echo $this->html('grid.selectlist', 'stream.translations.azurelocation', $this->config->get('stream.translations.azurelocation'), array(
								array('value' => 'australiaeast', 'text' => 'australiaeast'),
								array('value' => 'brazilsouth', 'text' => 'brazilsouth'),
								array('value' => 'canadacentral', 'text' => 'canadacentral'),
								array('value' => 'centralindia', 'text' => 'centralindia'),
								array('value' => 'centralus', 'text' => 'centralus'),
								array('value' => 'centraluseuap', 'text' => 'centraluseuap'),
								array('value' => 'eastasia', 'text' => 'eastasia'),
								array('value' => 'eastus', 'text' => 'eastus'),
								array('value' => 'eastus2', 'text' => 'eastus2'),
								array('value' => 'francecentral', 'text' => 'francecentral'),
								array('value' => 'japaneast', 'text' => 'japaneast'),
								array('value' => 'japanwest', 'text' => 'japanwest'),
								array('value' => 'koreacentral', 'text' => 'koreacentral'),
								array('value' => 'northcentralus', 'text' => 'northcentralus'),
								array('value' => 'northeurope', 'text' => 'northeurope'),
								array('value' => 'southcentralus', 'text' => 'southcentralus'),
								array('value' => 'southeastasia', 'text' => 'southeastasia'),
								array('value' => 'uksouth', 'text' => 'uksouth'),
								array('value' => 'westcentralus', 'text' => 'westcentralus'),
								array('value' => 'westeurope', 'text' => 'westeurope'),
								array('value' => 'westus', 'text' => 'westus'),
								array('value' => 'westus2', 'text' => 'westus2'),
								array('value' => 'southafricanorth', 'text' => 'southafricanorth')
						)); ?>
					</div>
				</div>

				<?php echo $this->html('settings.toggle', 'stream.translations.explicit', 'COM_EASYSOCIAL_STREAM_SETTINGS_ALWAYS_SHOW_TRANSLATIONS_LINK'); ?>
			</div>
		</div>
	</div>
</div>
