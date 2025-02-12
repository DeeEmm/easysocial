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
			<?php echo $this->html('panel.heading', 'COM_EASYSOCIAL_GENERAL_SETTINGS_LINKS_GENERAL', '', '/administrators/configuration/story-links-parsing-behavior'); ?>

			<div class="panel-body">
				<?php echo $this->html('settings.toggle', 'links.cache.data', 'COM_EASYSOCIAL_LINKS_SETTINGS_CACHE_LINKS_DATA'); ?>
				<?php echo $this->html('settings.toggle', 'links.cache.images', 'COM_EASYSOCIAL_LINKS_SETTINGS_CACHE_SHARED_IMAGES'); ?>
				<?php echo $this->html('settings.toggle', 'links.cache.cleanup.enabled', 'COM_EASYSOCIAL_LINKS_SETTINGS_CACHE_AUTOMATED_CLEANUP', '', 'data-links-auto-purge'); ?>

				<div class="form-group <?php echo $this->config->get('links.cache.cleanup.enabled') ? '' : 't-hidden';?>" data-links-auto-purge-interval>
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_LINKS_SETTINGS_CACHE_AUTOMATED_CLEANUP_DURATION'); ?>

					<div class="col-md-7">
						<?php echo $this->html('grid.selectlist', 'links.cache.cleanup.duration', $this->config->get('links.cache.cleanup.duration'), array(
								array('value' => '1', 'text' => 'COM_EASYSOCIAL_LINKS_SETTINGS_CACHE_CLEANUP_1_MONTHS'),
								array('value' => '3', 'text' => 'COM_EASYSOCIAL_LINKS_SETTINGS_CACHE_CLEANUP_3_MONTHS'),
								array('value' => '6', 'text' => 'COM_EASYSOCIAL_LINKS_SETTINGS_CACHE_CLEANUP_6_MONTHS'),
								array('value' => '12', 'text' => 'COM_EASYSOCIAL_LINKS_SETTINGS_CACHE_CLEANUP_12_MONTHS')
							)); ?>
					</div>
				</div>

				<?php echo $this->html('settings.toggle', 'links.parser.validate', 'COM_EASYSOCIAL_LINKS_SETTINGS_CHECK_LINK_BEFORE_PARSING'); ?>
				<?php echo $this->html('settings.textarea', 'links.parser.tld', 'COM_ES_LINKS_SETTINGS_SUPPORTED_TLD', '', array('rows' => 10)); ?>
			</div>
		</div>
	</div>

	<div class="col-md-6">
	</div>
</div>
