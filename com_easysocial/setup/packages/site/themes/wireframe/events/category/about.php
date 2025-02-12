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
<div class="es-side-widget">
	<?php echo $this->html('widget.title', 'COM_EASYSOCIAL_EVENTS_RANDOM_EVENTS'); ?>

	<div class="es-side-widget__bd">
		<?php if ($events) { ?>
		<div class="o-flag-list">
			<?php foreach ($events as $event) { ?>
			<div class="o-flag">
				<div class="o-flag__image">
					<?php echo $this->html('avatar.mini', $event->getName(), $event->getPermalink(), $event->getAvatar(SOCIAL_AVATAR_SQUARE)); ?>
				</div>
				<div class="o-flag__body">
					<a href="<?php echo $event->getPermalink();?>" class="ed-user-name t-mb--sm"><?php echo $event->getName();?></a>
					<div class="t-text--muted">
						<i class="fa fa-users"></i> <?php echo JText::sprintf(ES::string()->computeNoun('COM_EASYSOCIAL_EVENTS_GUESTS', $event->getTotalGuests()), $event->getTotalGuests());?>
					</div>
				</div>
			</div>
			<?php } ?>
		</div>
		<?php } else { ?>
		<div class="t-text--muted">
			<?php echo JText::_('COM_EASYSOCIAL_EVENTS_NO_EVENTS_FOUND'); ?>
		</div>
		<?php } ?>
	</div>
</div>

<div class="es-side-widget">
	<?php echo $this->html('widget.title', 'COM_EASYSOCIAL_EVENTS_RANDOM_GUESTS'); ?>

	<div class="es-side-widget__bd">
		<?php if ($randomGuests) { ?>
			<?php echo $this->html('widget.users', $randomGuests); ?>
		<?php } else { ?>
			<div class="t-text--muted">
				<?php echo JText::_('COM_EASYSOCIAL_EVENTS_NO_GUESTS_FOUND'); ?>
			</div>
		<?php } ?>
	</div>
</div>

<div class="es-side-widget">
	<?php echo $this->html('widget.title', 'COM_EASYSOCIAL_EVENTS_RANDOM_ALBUMS'); ?>

	<div class="es-side-widget__bd">
		<?php echo $this->html('widget.albums', $randomAlbums, 'COM_EASYSOCIAL_EVENTS_NO_ALBUMS_FOUND'); ?>
	</div>
</div>
