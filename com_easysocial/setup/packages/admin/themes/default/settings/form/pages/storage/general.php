<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2016 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

$services = array(
				array('value' => 'joomla', 'text' => 'COM_EASYSOCIAL_STORAGE_SETTINGS_LOCAL_SERVER'),
				array('value' => 'amazon', 'text' => 'COM_EASYSOCIAL_STORAGE_SETTINGS_AMAZON')
			);
?>
<div class="row">
	<div class="col-md-6">

		<div class="panel">
			<?php echo $this->html('panel.heading', 'COM_EASYSOCIAL_GENERAL_SETTINGS_STORAGE_LOCATIONS'); ?>


			<div class="panel-body">
				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_STORAGE_SETTINGS_AVATARS'); ?>

					<div class="col-md-7">
						<?php echo $this->html('grid.selectlist', 'storage.avatars', $this->config->get('storage.avatars'), $services); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_STORAGE_SETTINGS_FILES'); ?>

					<div class="col-md-7">
						<?php echo $this->html('grid.selectlist', 'storage.files', $this->config->get('storage.files'), $services); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_STORAGE_SETTINGS_PHOTOS'); ?>

					<div class="col-md-7">
						<?php echo $this->html('grid.selectlist', 'storage.photos', $this->config->get('storage.photos'), $services); ?>

						<div class="<?php echo $this->config->get('storage.photos') == 'amazon' ? '' : 't-hidden'; ?>" data-amazon-photos>
							<?php echo $this->html('grid.checkbox', 'storage.amazon.upload.photo', $this->config->get('storage.amazon.upload.photo'), JText::_('COM_EASYSOCIAL_STORAGE_SETTINGS_PHOTOS_AMAZON_UPLOAD')); ?>
						</div>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_STORAGE_SETTINGS_VIDEOS'); ?>

					<div class="col-md-7">
						<?php echo $this->html('grid.selectlist', 'storage.videos', $this->config->get('storage.videos'), $services); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_ES_STORAGE_SETTINGS_AUDIO'); ?>

					<div class="col-md-7">
						<?php echo $this->html('grid.selectlist', 'storage.audios', $this->config->get('storage.audios'), $services); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_STORAGE_SETTINGS_IMAGES_FROM_LINKS'); ?>

					<div class="col-md-7">
						<?php echo $this->html('grid.selectlist', 'storage.links', $this->config->get('storage.links'), $services); ?>
					</div>
				</div>

			</div>
		</div>

		<div class="panel">
			<?php echo $this->html('panel.heading', 'COM_EASYSOCIAL_STORAGE_SETTINGS_MANAGEMENT'); ?>

			<div class="panel-body">

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_ES_STORAGE_SETTINGS_USAGE_ENABLE'); ?>

					<div class="col-md-7">
						<?php echo $this->html('form.toggler', 'storage.size.enable', $this->config->get('storage.size.enable')); ?>
					</div>
				</div>

				<?php echo $this->html('settings.textbox', 'storage.size.warning', 'COM_ES_STORAGE_SETTINGS_USAGE_WARNING', '', array('postfix' => '%'), '', 'input-short text-center'); ?>
			</div>
		</div>
	</div>

	<div class="col-md-6">

		<div class="panel" data-amazon>
			<?php echo $this->html('panel.heading', 'COM_EASYSOCIAL_STORAGE_SETTINGS_AMAZON'); ?>


			<div class="panel-body">
				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_STORAGE_SETTINGS_AMAZON_ACCESS_KEY'); ?>

					<div class="col-md-7">
						<?php echo $this->html('grid.inputbox', 'storage.amazon.access', $this->config->get('storage.amazon.access')); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_STORAGE_SETTINGS_AMAZON_SECRET_KEY'); ?>

					<div class="col-md-7">
						<?php echo $this->html('grid.inputbox', 'storage.amazon.secret', $this->config->get('storage.amazon.secret')); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_STORAGE_SETTINGS_AMAZON_BUCKET_PATH'); ?>

					<div class="col-md-7">
						<?php echo $this->html('grid.inputbox', 'storage.amazon.bucket', $this->config->get('storage.amazon.bucket')); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_STORAGE_SETTINGS_AMAZON_SSL'); ?>

					<div class="col-md-7">
						<?php echo $this->html('form.toggler', 'storage.amazon.ssl', $this->config->get('storage.amazon.ssl')); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_STORAGE_SETTINGS_DELETE_FILES_AFTER_UPLOAD'); ?>

					<div class="col-md-7">
						<?php echo $this->html('form.toggler', 'storage.amazon.delete', $this->config->get('storage.amazon.delete')); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_STORAGE_SETTINGS_AMAZON_TRANSFER_LIMIT'); ?>

					<div class="col-md-7">
						<?php echo $this->html('grid.inputbox', 'storage.amazon.limit', $this->config->get('storage.amazon.limit'), '', array('class' => 'input-short text-center')); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_STORAGE_SETTINGS_AMAZON_STORAGE_REGION'); ?>

					<div class="col-md-7">
						<?php echo $this->html('grid.selectlist', 'storage.amazon.region', $this->config->get('storage.amazon.region'), array(
								array('value' => 'us', 'text' => 'COM_EASYSOCIAL_STORAGE_SETTINGS_US_EAST_NORTHERN_VIRGINIA'),
								array('value' => 'us-east-2', 'text' => 'COM_EASYSOCIAL_STORAGE_SETTINGS_US_EAST_OHIO'),
								array('value' => 'us-west-2', 'text' => 'COM_EASYSOCIAL_STORAGE_SETTINGS_US_WEST_OREGON'),
								array('value' => 'us-west-1', 'text' => 'COM_EASYSOCIAL_STORAGE_SETTINGS_US_WEST_NORTHERN_CALIFORNIA'),
								array('value' => 'af-south-1', 'text' => 'COM_EASYSOCIAL_STORAGE_SETTINGS_AF_CAPE_TOWN'),
								array('value' => 'ap-east-1', 'text' => 'COM_EASYSOCIAL_STORAGE_SETTINGS_ASIA_PACIFIC_HONG_KONG'),
								array('value' => 'ap-south-1', 'text' => 'COM_EASYSOCIAL_STORAGE_SETTINGS_ASIA_PACIFIC_MUMBAI'),
								array('value' => 'ap-northeast-1', 'text' => 'COM_EASYSOCIAL_STORAGE_SETTINGS_ASIA_PACIFIC_TOKYO'),
								array('value' => 'ap-northeast-2', 'text' => 'COM_EASYSOCIAL_STORAGE_SETTINGS_ASIA_PACIFIC_SEOUL'),
								array('value' => 'ap-northeast-3', 'text' => 'COM_EASYSOCIAL_STORAGE_SETTINGS_ASIA_PACIFIC_OSAKA_LOCAL'),
								array('value' => 'ap-southeast-1', 'text' => 'COM_EASYSOCIAL_STORAGE_SETTINGS_ASIA_PACIFIC_SINGAPORE'),
								array('value' => 'ap-southeast-2', 'text' => 'COM_EASYSOCIAL_STORAGE_SETTINGS_ASIA_PACIFIC_SYDNEY'),
								array('value' => 'cn-north-1', 'text' => 'COM_EASYSOCIAL_STORAGE_SETTINGS_CN_BEIJING'),
								array('value' => 'cn-northwest-1', 'text' => 'COM_EASYSOCIAL_STORAGE_SETTINGS_CN_NINGXIA'),
								array('value' => 'eu-central-1', 'text' => 'COM_EASYSOCIAL_STORAGE_SETTINGS_EU_FRANKFURT'),
								array('value' => 'eu-north-1', 'text' => 'COM_EASYSOCIAL_STORAGE_SETTINGS_EU_STOCKHOLM'),
								array('value' => 'eu-west-1', 'text' => 'COM_EASYSOCIAL_STORAGE_SETTINGS_EU_IRELAND'),
								array('value' => 'eu-west-2', 'text' => 'COM_EASYSOCIAL_STORAGE_SETTINGS_EU_LONDON'),
								array('value' => 'eu-west-3', 'text' => 'COM_EASYSOCIAL_STORAGE_SETTINGS_EU_PARIS'),
								array('value' => 'sa-east-1', 'text' => 'COM_EASYSOCIAL_STORAGE_SETTINGS_SOUTH_AMERICA_SAU_PAULO'),
								array('value' => 'ca-central-1', 'text' => 'COM_ES_STORAGE_SETTINGS_CANADA_CENTRAL'),
								array('value' => 'me-south-1', 'text' => 'COM_ES_STORAGE_SETTINGS_ME_BAHRAIN'),
								array('value' => 'us-gov-west-1', 'text' => 'COM_ES_STORAGE_SETTINGS_AWS_GOVCLOUD_US'),
								array('value' => 'us-gov-east-1', 'text' => 'COM_ES_STORAGE_SETTINGS_AWS_GOVCLOUD_US_EAST')
							)); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_STORAGE_SETTINGS_AMAZON_STORAGE_CLASS'); ?>

					<div class="col-md-7">
						<?php echo $this->html('grid.selectlist', 'storage.amazon.class', $this->config->get('storage.amazon.class'), array(
								array('value' => 'standard', 'text' => 'COM_EASYSOCIAL_STORAGE_SETTINGS_STANDARD_STORAGE'),
								array('value' => 'reduced', 'text' => 'COM_EASYSOCIAL_STORAGE_SETTINGS_REDUCED_REDUNDANCY')
							)); ?>
					</div>
				</div>
			</div>
		</div>
	</div>

</div>
