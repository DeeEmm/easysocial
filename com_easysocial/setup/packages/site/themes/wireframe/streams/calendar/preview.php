<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div class="es-stream-embed is-calendar">
	<div>
		<div class="o-grid o-grid--center">
			<div class="o-grid__cell">
				<a href="<?php echo $calendar->getPermalink();?>" class="t-font-weight--bold"><?php echo $calendar->_('title');?></a>

				<div>
					<span><?php echo $calendar->getStartDate()->format($timeformat); ?></span> &mdash;
					<span><?php echo $calendar->getEndDate()->format($timeformat);?></span>
				</div>

				<?php if ($calendar->all_day) { ?>
				<div>
					<?php echo JText::_('APP_CALENDAR_ALL_DAY_EVENT'); ?>
				</div>
				<?php } ?>
			</div>
			<div class="o-grid__cell o-grid__cell--auto-size ">
				<div class="es-calendar-date">
					<div class="es-calendar-date__date">
						<?php echo $calendar->getStartDate()->format('j');?>
					</div>
					<div class="es-calendar-date__mth">
						<?php echo $calendar->getStartDate()->format('M');?>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="o-box--border">
		<?php echo nl2br($calendar->_('description'));?>
	</div>
</div>
