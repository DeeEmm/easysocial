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
<div id="es" class="mod-es mod-es-sidebar-polls <?php echo $moduleLib->getSuffix();?>"
	data-es-polls-filter
	data-cluster-id="<?php echo $cluster ? $cluster->id : '' ?>"
	data-cluster-type="<?php echo $cluster ? $cluster->getType() : '' ?>"
>
	<div class="es-sidebar" data-sidebar>
		<?php echo $this->render('module', 'es-polls-sidebar-top', 'site/dashboard/sidebar.module.wrapper'); ?>

		<?php if ($showCreateButton) { ?>
		<a href="<?php echo $createButtonLink;?>" class="btn btn-es-primary btn-block t-lg-mb--xl"><?php echo JText::_('COM_EASYSOCIAL_NEW_POLL');?></a>
		<?php } ?>

		<?php if ($showStatistics) { ?>
		<div class="es-side-widget">
			<?php echo $this->html('widget.title', 'COM_EASYSOCIAL_POLLS'); ?>

			<div class="es-side-widget__bd">
				<ul class="o-tabs o-tabs--stacked">
					<li class="o-tabs__item has-notice <?php echo $filter === 'mine' ? 'active' : '';?>" data-filter-item="mine">
						<a href="<?php echo $filters->mine; ?>" class="o-tabs__link" title="<?php echo JText::_('COM_EASYSOCIAL_POLLS_ALL_POLLS');?>">
							<?php echo JText::_('COM_EASYSOCIAL_POLLS_ALL_POLLS');?>
						</a>
						<span class="o-tabs__bubble" data-counter><?php echo $total;?></span>
						<div class="o-loader o-loader--sm"></div>
					</li>
				</ul>
			</div>
		</div>
		<?php } else { ?>
		<div class="es-side-widget">
			<?php echo $this->html('widget.title', 'COM_EASYSOCIAL_POLLS'); ?>

			<div class="es-side-widget__bd">
				<ul class="o-tabs o-tabs--stacked">
					<li class="o-tabs__item has-notice <?php echo $filter === 'all' ? 'active' : '';?>" data-filter-item="all">
						<a href="<?php echo $filters->all; ?>" class="o-tabs__link" title="<?php echo JText::_('COM_EASYSOCIAL_POLLS_ALL_POLLS');?>">
							<?php echo JText::_('COM_EASYSOCIAL_POLLS_ALL_POLLS');?>
						</a>
						<span class="o-tabs__bubble" data-counter><?php echo $total;?></span>
						<div class="o-loader o-loader--sm"></div>
					</li>

					<?php if ($this->my->id) { ?>
					<li class="o-tabs__item has-notice <?php echo $filter === 'mine' ? 'active' : '';?>" data-filter-item="mine">
						<a href="<?php echo $filters->mine; ?>" class="o-tabs__link" title="<?php echo JText::_('COM_EASYSOCIAL_POLLS_MY_POLLS');?>">
							<?php echo JText::_('COM_EASYSOCIAL_POLLS_MY_POLLS');?>
						</a>
						<span class="o-tabs__bubble" data-counter><?php echo $myTotal;?></span>
						<div class="o-loader o-loader--sm"></div>
					</li>
					<?php } ?>
				</ul>
			</div>
		</div>
		<?php } ?>

		<?php echo $this->render('module', 'es-polls-sidebar-bottom', 'site/dashboard/sidebar.module.wrapper'); ?>
	</div>
</div>