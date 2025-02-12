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
<?php if ($showRSVPButton) { ?>
<div class="o-btn-group" data-es-events-rsvp data-id="<?php echo $event->id; ?>" data-page-reload="<?php echo $forceReload;?>">
	<button type="button" class="btn btn-es-default-o btn-<?php echo $buttonSize;?> dropdown-toggle_" data-es-toggle="dropdown" data-button>
		<span data-text>
		<?php if ($isParticipant) { ?>
			<?php if ($isAttending) { ?>
				<?php echo JText::_('COM_EASYSOCIAL_EVENTS_GUEST_GOING'); ?>
			<?php } else if ($isMaybeAttending) { ?>
				<?php echo JText::_('COM_EASYSOCIAL_EVENTS_GUEST_MAYBE'); ?>
			<?php } else if ($isNotAttending) { ?>
				<?php echo JText::_('COM_EASYSOCIAL_EVENTS_GUEST_NOTGOING'); ?>
			<?php } else if ($isInvited) { ?>
				<?php echo JText::_('COM_EASYSOCIAL_EVENTS_RSVP'); ?>
			<?php } ?>

		<?php } else { ?>
			<?php echo JText::_('COM_EASYSOCIAL_EVENTS_RSVP_SHORT'); ?>
		<?php } ?>
		</span>

		&nbsp;<i class="fa fa-caret-down"></i>
	</button>

	<ul class="dropdown-menu dropdown-menu-<?php echo $dropdownPlacement;?> dropdown-menu--rsvp" >

		<?php if (!$isOver && ($seatsAvailable || $isAttending || $isMaybeAttending)) { ?>
			<li class="<?php echo $isAttending ? ' active' : '';?>" data-state="going">
				<a href="javascript:void(0);">
					<?php echo JText::_('COM_EASYSOCIAL_EVENTS_GUEST_GOING'); ?>
				</a>
			</li>

			<?php if ($event->getParams()->get('allowmaybe')) { ?>
			<li class="<?php echo $isMaybeAttending ? ' active' : '';?>" data-state="maybe">
				<a href="javascript:void(0);">
					<?php echo JText::_('COM_EASYSOCIAL_EVENTS_GUEST_MAYBE'); ?>
				</a>
			</li>
			<?php } ?>

			<?php if ($event->getParams()->get('allownotgoingguest') || $isParticipant) { ?>
			<li class="<?php echo $isNotAttending ? ' active' : '';?>" data-state="notgoing">
				<a href="javascript:void(0);">
					<?php echo JText::_('COM_EASYSOCIAL_EVENTS_GUEST_NOTGOING'); ?>
				</a>
			</li>
			<?php } ?>

		<?php } ?>

		<?php if($isOver) { ?>
			<li class="es-rsvp-notice">
				<?php echo JText::_('COM_EASYSOCIAL_EVENTS_NO_LONGER_AVAILABLE_FOR_RSVP'); ?>
			</li>
		<?php } ?>

		<?php if (!$isOver && !$seatsAvailable && (!$isAttending && !$isMaybeAttending)) { ?>
		<li class="t-lg-p--lg">
			<?php echo JText::_('COM_EASYSOCIAL_EVENTS_NO_SEATS_LEFT'); ?>
		</li>
		<?php } ?>

		<?php if (!$isOver && $seatsAvailable && (!$this->my->getAccess()->get('events.allow.join') && $this->my->getAccess()->exceeded('events.join', $this->my->getTotalEvents()))) { ?>
		<li class="t-lg-p--lg">
			<?php echo JText::_('COM_EASYSOCIAL_EVENTS_EXCEEDED_JOIN_LIMIT'); ?>
		</li>
		<?php } ?>

	</ul>
</div>
<?php } else { ?>

	<?php if ($event->isClusterEvent() && !$isClusterAllowed && $this->my->getAccess()->get('events.allow.join')) { ?>
		<button type="button" class="btn btn-es-default-o btn-<?php echo $buttonSize;?>"
			data-es-provide="popover"
			data-content="<?php echo JText::sprintf('COM_ES_CLUSTER_EVENT_LIKED_CLUSTER_FIRST_MESSAGE_' . strtoupper($eventClusterType), $eventClusterName); ?>"
			data-placement="bottom"
			data-html="true"
			>
			<span>
				<?php echo JText::_('COM_EASYSOCIAL_EVENTS_RSVP_SHORT'); ?>
			</span>
		</button>
	<?php } ?>

	<?php if ($isPending) { ?>
		<a href="javascript:void(0);" class="btn btn-es-default-o btn-sm" data-es-events-withdraw data-id="<?php echo $event->id;?>">
			<i class="fa fa-sign-out-alt"></i>&nbsp; <?php echo JText::_('COM_EASYSOCIAL_EVENTS_GUEST_WITHDRAW'); ?>
		</a>
	<?php } ?>

	<?php if ($showRequestToAttend) { ?>
		<a href="javascript:void(0);" class="btn btn-es-default-o btn-sm" data-es-events-request data-id="<?php echo $event->id;?>">
			<?php echo JText::_('COM_EASYSOCIAL_EVENTS_REQUEST_TO_ATTEND_THIS_EVENT'); ?>
		</a>
	<?php } ?>
<?php } ?>
