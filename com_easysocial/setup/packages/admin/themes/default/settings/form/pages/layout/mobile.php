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
			<?php echo $this->html('panel.heading', 'COM_ES_MOBILE_LAYOUT_SETTINGS'); ?>

			<div class="panel-body">
				<?php echo $this->html('settings.toggle', 'mobile.userscaling', 'COM_ES_MOBILE_ENABLE_USER_SCALE'); ?>
			</div>
		</div>
	</div>

	<div class="col-md-6">
		<div class="panel">
			<?php echo $this->html('panel.heading', 'COM_ES_MOBILE_SHORTCUT_SETTINGS'); ?>

			<div class="panel-body">
				<?php echo $this->html('settings.toggle', 'mobileshortcut.enabled', 'COM_ES_RENDER_MOBILE_SHORTCUT'); ?>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_ES_MOBILE_SHORTCUT_SETTINGS_ICON'); ?>

					<div class="col-md-7">
						<div class="mb-20">
							<div class="es-img-holder">
								<div class="es-img-holder__remove <?php echo !ES::hasOverride('mobile_icon') ? 't-hidden' : '';?>">
									<a href="javascript:void(0);" data-image-restore data-type='mobile_icon'>
										<i class="fa fa-times"></i>&nbsp; <?php echo JText::_('COM_ES_REMOVE'); ?>
									</a>
								</div>
								<img src="<?php echo ES::getMobileIcon(); ?>" width="120" data-image-source data-default="<?php echo ES::getMobileIcon(true);?>" />
							</div>
						</div>
						<div style="clear:both;" class="t-lg-mb--xl">
							<input type="file" name="mobile_icon" id="mobile_icon" class="input" style="width:265px;" data-uniform />
						</div>
					</div>
				</div>

				<?php echo $this->html('settings.textbox', 'mobileshortcut.name', 'COM_ES_MOBILE_SHORTCUT_SETTINGS_NAME'); ?>
				<?php echo $this->html('settings.textbox', 'mobileshortcut.shortname', 'COM_ES_MOBILE_SHORTCUT_SETTINGS_SHORTNAME'); ?>
				<?php echo $this->html('settings.colorpicker', 'mobileshortcut.theme', 'COM_ES_MOBILE_SHORTCUT_SETTINGS_THEME'); ?>
			</div>
		</div>
	</div>
</div>
