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
<div id="es" class="mod-es mod-es-sidebar-notes <?php echo $moduleLib->getSuffix();?>" data-es-container data-profile-user-apps-notes>
	<div class="es-sidebar">
		<?php if ($user->isViewer()) { ?>
			<a href="javascript:void(0);" class="btn btn-es-primary btn-block t-lg-mb--xl" data-notes-create><?php echo JText::_('APP_NOTES_NEW_NOTE_BUTTON'); ?></a>
		<?php } ?>

		<div class="es-side-widget">
			<?php echo $this->html('widget.title', 'COM_ES_STATISTICS'); ?>

			<div class="es-side-widget__bd">
				<ul class="o-nav o-nav--stacked">
					<li class="o-nav__item t-lg-mb--sm">
						<span class="o-nav__link t-text--muted">
							<i class="es-side-widget__icon fa fa-sticky-note-o t-lg-mr--md"></i>
							<b><?php echo $total;?></b> <?php echo JText::_('COM_ES_NOTES');?>
						</span>
					</li>
				</ul>
			</div>
		</div>
	</div>
</div>
