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
				<?php echo $this->html('settings.toggle', 'sharer.enabled', 'COM_ES_ENABLE_SHARER'); ?>
				<?php echo $this->html('settings.toggle', 'sharer.users', 'COM_ES_ENABLE_SHARER_USERS'); ?>
			</div>
		</div>
	</div>

	<div class="col-md-6">
		<div class="panel">
			<?php echo $this->html('panel.heading', 'COM_ES_SHARER_EMBED_BUTTON'); ?>

			<div class="panel-body">
				<?php echo $this->html('settings.dropdown', 'sharer.style', 'COM_ES_SHARER_EMBED_BUTTON_STYLE', '', [
					'full' => 'Icon &amp; Text',
					'text' => 'Text Only',
					'icon' => 'Icon Only'
				], 'data-es-embed-style'); ?>

				<?php echo $this->html('settings.colorpicker', 'sharer.buttoncolour', 'COM_ES_SHARER_EMBED_BUTTON_COLOUR', '', '#445AB5'); ?>

				<div class="form-group" data-es-embed-icon>
					<?php echo $this->html('panel.label', 'COM_ES_SHARER_EMBED_LOGO'); ?>

					<div class="col-md-7">
						<div class="mb-20">
							<div class="es-img-holder">
								<div class="es-img-holder__remove <?php echo !ES::hasOverride('sharer_logo') ? 't-hidden' : '';?>">
									<a href="javascript:void(0);" data-image-restore data-type="sharer_logo">
										<i class="fa fa-times"></i>&nbsp; <?php echo JText::_('COM_ES_REMOVE'); ?>
									</a>
								</div>
								<img src="<?php echo ES::sharer()->getLogo(); ?>" data-image-source data-default="<?php echo ES::sharer()->getDefaultLogo();?>" width="120" />
							</div>
						</div>
						<div style="clear:both;" class="t-lg-mb--xl">
							<input type="file" name="sharer_logo" id="sharer_logo" class="input" style="width:265px;" data-uniform />
						</div>
					</div>
				</div>

				<?php echo $this->html('settings.textbox', 'sharer.text', 'COM_ES_SHARER_EMBED_TEXT', '', array(
						'wrapperClass' => $this->config->get('sharer.style') == 'full' || $this->config->get('sharer.style') == 'text' ? '' : 't-hidden'), '', '', 'data-es-embed-text');
					?>
				<?php echo $this->html('settings.colorpicker', 'sharer.textcolour', 'COM_ES_SHARER_EMBED_TEXT_COLOUR', '', '#FFFFFF', 'data-es-embed-text', array(
						'wrapperClass' => $this->config->get('sharer.style') == 'full' || $this->config->get('sharer.style') == 'text' ? '' : 't-hidden'
					)); ?>
				<?php echo $this->html('settings.colorpicker', 'sharer.popupcolour', 'COM_ES_SHARER_POPUP_WINDOW_COLOUR', '', '#F5F5F5'); ?>
				<?php echo $this->html('settings.colorpicker', 'sharer.popupbordercolour', 'COM_ES_SHARER_POPUP_BORDER_COLOUR', '', '#DEDEDE'); ?>
				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_ES_SHARER_EMBED_CODES'); ?>

					<div class="col-md-7">
						<textarea class="o-form-control" style="min-height: 250px;"><?php echo $this->output('site/sharer/example');?></textarea>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
