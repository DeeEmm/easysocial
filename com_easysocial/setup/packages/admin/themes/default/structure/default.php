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
<div id="es" class="es-backend <?php echo $class;?>">

	<?php if ($privacyUnsynedCount) { ?>
	<div class="app-alert o-alert o-alert--danger" style="margin-bottom: 0;padding: 15px 24px;font-size: 12px;">
		<div class="row-table">
			<div class="col-cell cell-tight">
				<i class="app-alert__icon fa fa-bolt"></i>
			</div>
			<div class="col-cell alert-message">
				<?php echo JText::_('COM_ES_MEDIA_PRIVACY_OUT_OF_SYNCED');?>
			</div>
			<div class="col-cell cell-tight">
				<a href="<?php echo JRoute::_('index.php?option=com_easysocial&view=maintenance&layout=privacy');?>" class="btn btn-es-default">
					<b><?php echo JText::_('COM_ES_MEDIA_PRIVACY_FIX_NOW_BUTTON');?></b>
				</a>
			</div>
		</div>
	</div>
	<?php } ?>

	<div class="app-alert o-alert o-alert--danger t-hidden" style="margin-bottom: 0;padding: 15px 24px;font-size: 12px;" data-outdated-banner>
		<div class="row-table">
			<div class="col-cell cell-tight">
				<i class="app-alert__icon fa fa-bolt"></i>
			</div>
			<div class="col-cell alert-message">
				<?php echo JText::_('COM_EASYSOCIAL_OUTDATED_VERSION');?>
			</div>
			<div class="col-cell cell-tight">
				<a href="<?php echo JRoute::_('index.php?option=com_easysocial&controller=system&task=upgrade');?>" class="btn btn-es-default">
					<b><i class="fa fa-bolt"></i>&nbsp; <?php echo JText::_('COM_EASYSOCIAL_UPDATE_NOW_BUTTON');?></b>
				</a>
			</div>
		</div>
	</div>

	<?php if ($tmpl != 'component') { ?>
		<div class="app " data-es-app>

			<?php if ($showSidebar) { ?>
				<?php echo $sidebar; ?>
			<?php } ?>

			<div class="app-content">
				<?php echo ES::info()->toHTML(); ?>

				<div class="app-head">
					<h2 data-structure-heading><?php echo $page->heading; ?></h2>
					<p data-structure-description><?php echo $page->description; ?></p>

					<div class="<?php echo $customAction ? '' : ' t-hidden'; ?>" data-structure-custom-action>
						<?php echo $customAction; ?>
					</div>
				</div>
				<?php if ($message) { ?>
				<div class="socialNotice app-content__alert o-alert o-alert--<?php echo $message->type;?>">
					<?php echo $message->message;?>
				</div>
				<?php } ?>
				<div class="app-body accordion">
					<?php echo ES::profiler()->toHTML(); ?>

					<?php echo $html; ?>
				</div>

				<?php if ($help) { ?>
				<div id="help-button-template" style="display: none;">
					<div class="btn-wrapper" id="toolbar-help">
						<a href="<?php echo $help;?>" target="_blank" class="btn btn-small"><?php echo JText::_('JHELP'); ?></a>
					</div>
				</div>
				<?php } ?>

			</div>
		</div>
	<?php } else { ?>
		<?php echo ES::info()->toHTML(); ?>

		<?php echo $html; ?>

	<?php } ?>
</div>
