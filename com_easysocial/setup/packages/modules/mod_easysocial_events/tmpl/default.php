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
<div id="es" class="mod-es <?php echo $lib->getSuffix();?>">
	<div class="mod-es-list--vertical">
		<?php foreach ($events as $event) { ?>
		<div class="mod-es-item">
			<div class="o-flag">
				<?php if ($params->get('display_avatar', true)) { ?>
					<div class="o-flag__img t-lg-mr--md">
						<?php echo ES::themes()->html('avatar.cluster', $event, 'default'); ?>
					</div>
				<?php } ?>
				<div class="o-flag__body">
					<a href="<?php echo $event->getPermalink();?>" class="mod-es-title">
						<?php echo $event->getName();?>
					</a>

					<div class="mod-es-meta">
						<ol class="g-list-inline g-list-inline--delimited">
							<?php if ($params->get('display_category' , true)) { ?>
							<li>
								<i class="fa fa-folder"></i>&nbsp;
								<a href="<?php echo $event->getCategory()->getPermalink(); ?>"><?php echo $event->getCategory()->getTitle(); ?></a>
							</li>
							<?php } ?>
							<?php if ($params->get('display_member_counter', true)) { ?>
							<li>
								<i class="fa fa-users"></i>&nbsp;
								<?php echo JText::sprintf(ES::string()->computeNoun('MOD_EASYSOCIAL_EVENTS_GUEST_COUNT', $event->getTotalGuests()), $event->getTotalGuests());?>
							</li>
							<?php } ?>
							<li>
								<i class="fa fa-calendar"></i>&nbsp;
								<?php echo $event->getStartEndDisplay(array('end' => false)); ?>
							</li>
						</ol>
					</div>

					<?php if ($params->get('display_rsvp', true)) { ?>
						<div class="mod-es-action">
							<?php echo ES::themes()->html('event.action', $event); ?>
						</div>
					<?php } ?>
				</div>
			</div>
		</div>
		<?php } ?>
	</div>
	<?php if ($params->get('display_alllink', true)) { ?>
	<div class="mod-es-action">
		<a href="<?php echo ESR::events(); ?>" class="btn btn-es-default-o btn-sm btn-block"><?php echo JText::_('MOD_EASYSOCIAL_EVENTS_ALL_EVENT'); ?></a>
	</div>
	<?php } ?>
</div>
