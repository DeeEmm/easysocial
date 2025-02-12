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
<div id="es" class="mod-es mod-es-sidebar-event-guest <?php echo $moduleLib->getSuffix();?>"
	data-es-member-filter
	data-cluster-id="<?php echo $cluster ? $cluster->id : '' ?>"
	data-cluster-type="<?php echo $cluster ? $cluster->getType() : '' ?>"
>
	<div class="es-sidebar" data-sidebar>
		<?php echo $this->render('module', 'es-event-guest-sidebar-top', 'site/dashboard/sidebar.module.wrapper'); ?>

		<div class="es-side-widget">
			<?php echo $this->html('widget.title', 'COM_ES_FILTERS'); ?>

			<div class="es-side-widget__bd">
				<ul class="o-tabs o-tabs--stacked feed-items">
					<li class="o-tabs__item has-notice <?php echo $active == 'going' ? ' active' : '';?>" data-filter data-type="going">
						<a href="javascript:void(0);" class="o-tabs__link">
							<?php echo JText::_('APP_EVENT_GUESTS_FILTER_GOING');?>
						</a>
						<div class="o-tabs__bubble" data-counter><?php echo $counters->going;?></div>
					</li>

					<?php if ($cluster->getParams()->get('allowmaybe', true)) { ?>
					<li class="o-tabs__item has-notice <?php echo $active == 'maybe' ? ' active' : '';?>" data-filter data-type="maybe">
						<a href="javascript:void(0);" class="o-tabs__link">
							<?php echo JText::_('APP_EVENT_GUESTS_FILTER_MAYBE');?>
						</a>
						<div class="o-tabs__bubble" data-counter><?php echo $counters->maybe;?></div>
					</li>
					<?php } ?>

					<?php if ($cluster->getParams()->get('allownotgoingguest', true)) { ?>
					<li class="o-tabs__item has-notice <?php echo $active == 'notgoing' ? ' active' : '';?>" data-filter data-type="notgoing">
						<a href="javascript:void(0);" class="o-tabs__link">
							<?php echo JText::_('APP_EVENT_GUESTS_FILTER_NOTGOING');?>
						</a>
						<div class="o-tabs__bubble" data-counter><?php echo $counters->notgoing;?></div>
					</li>
					<?php } ?>

					<li class="o-tabs__item has-notice <?php echo $active == 'admin' ? ' active' : '';?>" data-filter data-type="admin">
						<a href="javascript:void(0);" class="o-tabs__link">
							<?php echo JText::_('APP_EVENT_GUESTS_FILTER_ADMINS');?>
						</a>
						<div class="o-tabs__bubble" data-counter><?php echo $counters->admins;?></div>
					</li>

					<?php if ($counters->invited) { ?>
					<li class="o-tabs__item has-notice <?php echo $active == 'invited' ? ' active' : '';?>" data-filter data-type="invited">
						<a href="javascript:void(0);" class="o-tabs__link">
							<?php echo JText::_('APP_EVENT_GUESTS_FILTER_INVITED');?>
						</a>
						<div class="o-tabs__bubble" data-counter><?php echo $counters->invited;?></div>
					</li>
					<?php } ?>

					<?php if ($counters->pending) { ?>
					<li class="o-tabs__item has-notice <?php echo $active == 'pending' ? ' active' : '';?> <?php echo $counters->pending ? 'has-notice' : '';?>" data-filter data-type="pending">
						<a href="javascript:void(0);" class="o-tabs__link">
							<?php echo JText::_('APP_EVENT_GUESTS_FILTER_PENDING');?>
							<div class="o-tabs__bubble" data-counter><?php echo $counters->pending;?></div>
						</a>
					</li>
					<?php } ?>
				</ul>
			</div>
		</div>

		<?php echo $this->render('module', 'es-event-guest-sidebar-bottom', 'site/dashboard/sidebar.module.wrapper'); ?>
	</div>
</div>

<script>

EasySocial.require()
.script('site/members/filter')
.done(function($) {
	$('body').implement(EasySocial.Controller.Members.Filter);
});

</script>
