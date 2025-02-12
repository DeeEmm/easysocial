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
?>
<div class="row">
	<div class="col-md-6">
		<div class="panel">
			<?php echo $this->html('panel.heading', 'COM_EASYSOCIAL_PAGES_SETTINGS_LAYOUT'); ?>

			<div class="panel-body">

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_ES_SETTINGS_DEFAULT_AVATAR'); ?>

					<div class="col-md-7">
						<div class="mb-20">
							<div class="es-img-holder">
								<div class="es-img-holder__remove <?php echo !ES::hasOverride('page_avatar') ? 't-hidden' : '';?>">
									<a href="javascript:void(0);" data-image-restore data-type="page_avatar">
										<i class="fa fa-times"></i>
									</a>
								</div>
								<img src="<?php echo ES::getDefaultAvatar('page', 'medium'); ?>" width="64" height="64" data-image-source data-default="<?php echo ES::getDefaultAvatar('page', 'medium', true);?>" />
							</div>
						</div>
						<div style="clear:both;" class="t-lg-mb--xl">
							<input type="file" name="page_avatar" id="page_avatar" class="input" style="width:265px;" data-uniform />
						</div>

						<br />

						<div class="help-block">
							<?php echo JText::_('COM_ES_SETTINGS_DEFAULT_AVATAR_SIZE_NOTICE'); ?>
						</div>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_ES_SETTINGS_DEFAULT_COVER'); ?>

					<div class="col-md-7">
						<div class="mb-20">
							<div class="es-img-holder">
								<div class="es-img-holder__remove <?php echo !ES::hasOverride('page_cover') ? 't-hidden' : '';?>">
									<a href="javascript:void(0);" data-image-restore data-type="page_cover">
										<i class="fa fa-times"></i>
									</a>
								</div>
								<img src="<?php echo ES::getDefaultCover('page'); ?>" width="256" height="98" data-image-source data-default="<?php echo ES::getDefaultCover('page', true);?>" />
							</div>
						</div>

						<div style="clear:both;" class="t-lg-mb--xl">
							<input type="file" name="page_cover" id="page_cover" class="input" style="width:265px;" data-uniform />
						</div>

						<br />

						<div class="help-block">
							<?php echo JText::_('COM_ES_SETTINGS_DEFAULT_COVER_SIZE_NOTICE'); ?>
						</div>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_SETTINGS_DEFAULT_TAB'); ?>

					<div class="col-md-7">
						<?php echo $this->html('grid.selectlist', 'pages.item.display', $this->config->get('pages.item.display'), array(
									array('value' => 'timeline', 'text' => 'COM_EASYSOCIAL_SETTINGS_DEFAULT_TAB_TIMELINE'),
									array('value' => 'info', 'text' => 'COM_EASYSOCIAL_USERS_SETTINGS_PROFILE_DISPLAY_ABOUT')
								)); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_PAGES_SETTINGS_DEFAULT_EDITOR'); ?>

					<div class="col-md-7">
						<?php echo $this->html('form.editors', 'pages.editor', $this->config->get('pages.editor')); ?>
					</div>
				</div>

				<?php echo $this->html('settings.toggle', 'pages.hits.display', 'COM_EASYSOCIAL_PAGES_SETTINGS_ENABLE_HIT_COUNTER'); ?>
				<?php echo $this->html('settings.toggle', 'pages.layout.categoryheaders', 'COM_EASYSOCIAL_THEMES_WIREFRAME_PAGES_CATEGORY_HEADERS'); ?>
				<?php echo $this->html('settings.toggle', 'pages.layout.listingsdesc', 'COM_ES_THEMES_WIREFRAME_CLUSTERS_SHOW_DESCRIPTION_LISTINGS'); ?>
				<?php echo $this->html('settings.toggle', 'pages.layout.description', 'COM_ES_THEMES_WIREFRAME_CLUSTERS_SHOW_DESCRIPTION'); ?>
			</div>
		</div>
	</div>
</div>
