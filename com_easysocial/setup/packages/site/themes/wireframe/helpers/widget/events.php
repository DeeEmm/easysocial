<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2016 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<?php if ($events) { ?>
<ul class="o-nav o-nav--stacked">
	<?php foreach ($events as $event) { ?>
	<li class="o-nav__item ">
		<div class="es-side-widget__event t-lg-mb--xl">
			<div class="o-flag">
				<div class="o-flag__image o-flag--top">
					<div class="es-side-calendar-date <?php echo ES::config()->get('layout.avatar.style') == 'rounded' ? 'es-card__calendar-date--rounded' : '';?>">
						<div class="es-side-calendar-date__date">
							<?php echo $event->getEventStart()->format('d'); ?>
						</div>
						<div class="es-side-calendar-date__mth">
							<?php echo $event->getEventStart()->format('M'); ?>
						</div>
					</div>  
				</div>
				<div class="o-flag__body">
					<a href="<?php echo $event->getPermalink();?>" class="es-side-calendar-date__title"><?php echo $event->getName();?></a>
					<ol class="g-list-inline g-list-inline--delimited es-side-widget__meta-list">
						<li data-breadcrumb=".">
							<a href="<?php echo $event->getCategory()->getPermalink();?>">
								<?php echo $event->getCategory()->getTitle();?>
							</a>
						</li>
						<li data-breadcrumb=".">
							<a href="<?php echo $event->getPermalink();?>">
								<?php echo JText::sprintf(ES::string()->computeNoun('COM_EASYSOCIAL_EVENTS_TOTAL_GUESTS', $event->getTotalGoing()), $event->getTotalGoing()); ?>
							</a>
						</li>
					</ol>
					<div class="t-lg-mt--md">
						<?php echo $this->html('event.action', $event, 'left', false, 'xs'); ?>
					</div>
				</div>
			</div>    
		</div>
	</li>
	<?php } ?>
</ul>
<?php } else { ?>
<div>
	<?php echo $emptyMessage; ?>
</div>
<?php } ?>