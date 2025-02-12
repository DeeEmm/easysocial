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
<div id="es" class="mod-es mod-es-sidebar-discussions <?php echo $moduleLib->getSuffix();?>"
	data-es-discussions-filter
	data-cluster-id="<?php echo $cluster ? $cluster->id : '' ?>"
	data-cluster-type="<?php echo $cluster ? $cluster->getType() : '' ?>"
>
	<div class="es-sidebar" data-sidebar>
		<?php echo $this->render('module', 'es-discussions-sidebar-top', 'site/dashboard/sidebar.module.wrapper'); ?>

		<?php if ($showCreateButton) { ?>
		<a href="<?php echo $createButtonLink; ?>" class="btn btn-es-primary btn-block t-lg-mb--xl">
			<?php echo JText::_('APP_GROUP_DISCUSSIONS_CREATE_DISCUSSION'); ?>
		</a>
		<?php } ?>

		<div class="es-side-widget">
			<?php echo $this->html('widget.title', 'COM_ES_FILTERS'); ?>

			<div class="es-side-widget__bd">
				<ul class="o-tabs o-tabs--stacked">
					<li class="o-tabs__item has-notice active" data-filter-item="all">
						<a href="<?php echo $filters->all; ?>" class="o-tabs__link">
							<?php echo JText::_('APP_GROUP_DISCUSSIONS_FILTER_ALL');?>
						</a>
						<div class="o-tabs__bubble"><?php echo $counters['total'];?></div>
					</li>
					<li class="o-tabs__item has-notice" data-filter-item="unanswered">
						<a href="<?php echo $filters->unanswered; ?>" class="o-tabs__link">
							<?php echo JText::_('APP_GROUP_DISCUSSIONS_FILTER_UNANSWERED');?>
						</a>
						<div class="o-tabs__bubble"><?php echo $counters['unanswered'];?></div>
					</li>

					<li class="o-tabs__item has-notice" data-filter-item="resolved">
						<a href="<?php echo $filters->resolved; ?>" class="o-tabs__link">
							<?php echo JText::_('APP_GROUP_DISCUSSIONS_FILTER_RESOLVED');?>
						</a>
						<div class="o-tabs__bubble"><?php echo $counters['resolved'];?></div>
					</li>

					<li class="o-tabs__item has-notice" data-filter-item="unresolved">
						<a href="<?php echo $filters->unresolved; ?>" class="o-tabs__link">
							<?php echo JText::_('APP_GROUP_DISCUSSIONS_FILTER_UNRESOLVED');?>
						</a>
						<div class="o-tabs__bubble"><?php echo $counters['unresolved'];?></div>
					</li>

					<li class="o-tabs__item has-notice" data-filter-item="locked">
						<a href="<?php echo $filters->locked; ?>" class="o-tabs__link">
							<?php echo JText::_('APP_GROUP_DISCUSSIONS_FILTER_LOCKED');?>
						</a>
						<div class="o-tabs__bubble"><?php echo $counters['locked'];?></div>
					</li>
				</ul>
			</div>
		</div>

		<?php echo $this->render('module', 'es-discussions-sidebar-bottom', 'site/dashboard/sidebar.module.wrapper'); ?>
	</div>
</div>
