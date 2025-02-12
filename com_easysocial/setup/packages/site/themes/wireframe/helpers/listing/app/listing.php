<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div class="es-apps-item es-island" data-item data-id="<?php echo $app->id; ?>">
	<div class="es-apps-item__bd">
		<div class="o-flag">
			<div class="o-flag__image o-flag--top">
				<?php echo $this->html('avatar.mini', $app->getAppTitle(), '', '', 'default', 'es-app-item__avatar', '', false, 'square', $app->getTextAvatar()); ?>
			</div>

			<div class="o-flag__body">
				<div class="es-apps-item__title"><?php echo $app->getAppTitle();?></div>
				<div class="es-apps-item__meta">
					v<?php echo $app->getMeta()->version; ?>
				</div>
				<div class="es-apps-item__desp"><?php echo JText::_($app->getUserDesc()); ?></div>
			</div>
		</div>
	</div>
	<div class="es-apps-item__ft es-bleed--bottom">
		<?php if (!$app->default) { ?>
			<a class="btn btn-es-danger-o btn-sm" <?php echo !$app->isInstalled() ? 'style="display:none;"' : ''; ?> href="javascript:void(0);" data-uninstall>
				<?php echo JText::_('COM_EASYSOCIAL_UNINSTALL_BUTTON'); ?>
			</a>

			<a class="btn btn-es-primary-o btn-sm" <?php echo $app->isInstalled() ? 'style="display:none;"' : ''; ?> href="javascript:void(0);" data-install>
				<?php echo JText::_('COM_EASYSOCIAL_INSTALL_BUTTON'); ?>
			</a>

			<?php if ($app->hasUserSettings()) { ?>
			<a class="btn btn-es-default-o btn-sm <?php echo !$app->isInstalled() ? ' t-hidden ' : '';?>" data-settings>
				<i class="fa fa-cog"></i>
			</a>
			<?php } ?>
		<?php } else { ?>
			<span class="t-fs--sm"><?php echo JText::_('COM_ES_DEFAULT_APPS'); ?></span>
		<?php } ?>
	</div>
</div>
