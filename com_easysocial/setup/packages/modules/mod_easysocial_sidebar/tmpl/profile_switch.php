<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div id="es" class="mod-es mod-es-sidebar <?php echo $this->lib->getSuffix();?>">
	<div class="es-sidebar" data-sidebar>
		<?php echo $this->lib->render('module' , 'es-profile-edit-sidebar-top' , 'site/dashboard/sidebar.module.wrapper'); ?>

		<div class="es-side-widget">
			<?php echo $this->lib->html('widget.title', 'COM_EASYSOCIAL_PROFILE_SIDEBAR_ABOUT'); ?>

			<div class="es-side-widget__bd">
				<ul class="o-tabs o-tabs--stacked">
					<?php $i = 0; ?>
					<?php foreach ($steps as $step) { ?>
						<li class="o-tabs__item<?php echo $i == 0 ? ' active' :'';?>" data-profile-edit-fields-step data-for="<?php echo $step->id;?>" data-actions="1">
							<a class="o-tabs__link" href="javascript:void(0);"><?php echo $step->get('title'); ?>
								<i class=" o-tabs__indicator-error-icon"></i>
							</a>
						</li>
						<?php $i++; ?>
					<?php } ?>
				</ul>
			</div>
		</div>

		<div class="es-side-widget">
			<div class="o-box t-lg-mt--lg">
				<div>
					<span class="t-font-weight--bold">
						<?php echo JText::_('COM_EASYSOCIAL_PROFILE_SWITCH_SIDEBAR_NOTE_TITLE'); ?>
					</span>
					<div>
						<?php echo JText::_('COM_EASYSOCIAL_PROFILE_SWITCH_SIDEBAR_NOTE_DESC'); ?>
					</div>
				</div>
			</div>
		</div>

		<?php echo $this->lib->render('module' , 'es-profile-edit-sidebar-bottom' , 'site/dashboard/sidebar.module.wrapper'); ?>
	</div>
</div>
