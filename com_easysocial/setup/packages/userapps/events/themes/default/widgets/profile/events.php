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
<div class="es-side-widget is-module">
	<?php echo $this->html('widget.title', 'APP_USER_EVENTS_WIDGET_EVENTS'); ?>

	<div class="es-side-widget__bd">
		<?php if (($attendingTotal > 0) && ($createdTotal > 0)) { ?>
		<ul class="g-list-inline g-list-inline--dashed t-lg-mb--md">
			<?php if ($attendingTotal > 0) { ?>
			<li class="active">
				<a href="#es-attending" role="tab" data-es-toggle="tab">
					<span class="widget-label"><?php echo JText::_('APP_USER_EVENTS_WIDGET_ATTENDING_EVENTS'); ?></span>
				</a>
			</li>
			<?php } ?>

			<?php if ($createdTotal > 0) { ?>
			<li>
				<a href="#es-created" role="tab" data-es-toggle="tab">
					<?php if (!empty($createdTotal) && $allowCreate) { ?>
					<span class="widget-label"><?php echo JText::_('APP_USER_EVENTS_WIDGET_CREATED_EVENTS'); ?></span>
					<?php } ?>
				</a>
			</li>
			<?php } ?>
		</ul>
		<?php } ?>

		<div class="tab-content">

			<?php if ($attendingTotal > 0) { ?>
			<div class="tab-pane active" id="es-attending">
				<?php echo $this->html('widget.events', $attendingEvents); ?>
			</div>
			<?php } ?>

			<?php if ($createdTotal > 0) { ?>
			<div class="tab-pane <?php echo !$attendingTotal && $createdTotal > 0 ? 'active' : '';?>" id="es-created">
				<?php if (!empty($createdTotal) && $allowCreate) { ?>
					<?php echo $this->html('widget.events', $createdEvents); ?>
				<?php } ?>
			</div>
			<?php } ?>
		</div>

	</div>

	<?php if ($total > 0) { ?>
	<div class="es-side-widget__ft">
		<?php echo $this->html('widget.viewAll', 'COM_ES_VIEW_ALL', $viewAll); ?>
	</div>
	<?php } ?>
</div>
