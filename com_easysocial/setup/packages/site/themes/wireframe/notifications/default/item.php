<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<?php if ($items) { ?>
	<?php foreach ($items as $group => $notifications) { ?>
	<div class="es-noti-list">
		<div class="es-noti__date t-mt--lg t-mb--lg"><?php echo $group;?></div>
		<?php foreach ($notifications as $item) { ?>
		<div class="es-noti__item es-bleed--middle is-<?php echo $item->type;?><?php echo $item->state == SOCIAL_NOTIFICATION_STATE_READ ? ' is-read' : '';?><?php echo $item->state == SOCIAL_NOTIFICATION_STATE_HIDDEN ? ' is-hidden' : '';?><?php echo $item->state == SOCIAL_NOTIFICATION_STATE_UNREAD ? ' is-unread' : '';?>"
			data-notifications-list-item
			data-id="<?php echo $item->id;?>"
		>
			<div class="es-noti__noclick"></div>
			<div class="es-noti__item-indicator"></div>

			<div class="o-flag es-noti__item-content">
				<div class="o-flag__image o-flag--top t-lg-pr--lg">
					<?php if (isset($item->userOverride)) { ?>
					<?php echo $this->html('avatar.user', $item->userOverride); ?>
					<?php } else { ?>
					<?php echo $this->html('avatar.user', $item->user); ?>
					<?php } ?>
				</div>
				<div class="o-flag__body o-flag--top">
					<a class="es-noti__title t-lg-mb--md" href="<?php echo ESR::notifications(array('id' => $item->id, 'layout' => 'route') );?>"><?php echo JText::_($item->title);?></a>

					<?php if ($item->image || $item->content) { ?>
					<div class="t-lg-mb--sm">
						<?php if ($item->image) { ?>
							<a href="<?php echo ESR::notifications(array('id' => $item->id, 'layout' => 'route'));?>" class="es-noti__item-embed">
								<img src="<?php echo $item->image;?>" class="" />
							</a>
						<?php } ?>

						<?php if ($item->content) { ?>
							<div class="es-noti__desp t-mb--md">
								<?php echo $item->content; ?>
							</div>
						<?php } ?>
					</div>
					<?php } ?>

					<div class="es-noti__meta">
						<ol class="g-list-inline g-list-inline--delimited es-noti__meta-list">
							<?php if (isset($item->reaction) && !empty($item->reaction)) { ?>
								<li>
									<div class="es-reaction-list-wrapper">
										<?php foreach ($item->reaction as $reaction) { ?><div><i class="es-icon-reaction es-icon-reaction--sm es-icon-reaction--<?php echo $reaction; ?>"></i></div><?php } ?>
									</div>
								</li>
							<?php } ?>
							<li>
								<a href="<?php echo ESR::notifications(array('id' => $item->id, 'layout' => 'route'));?>" class="t-text--muted"><time><?php echo $item->since;?></time></a>
							</li>
						</ol>
					</div>
				</div>
			</div>

			<div role="group" class="o-btn-group es-noti__item-action">
				<a class="btn btn-es-primary-o btn-sm" href="javascript:void(0);" data-read data-es-provide="tooltip" data-original-title="<?php echo JText::_('COM_EASYSOCIAL_NOTIFICATIONS_MARK_READ');?>">
					<i class="fa fa-check"></i>
				</a>

				<a class="btn btn-es-default-o btn-sm" href="javascript:void(0);" data-unread data-es-provide="tooltip" data-original-title="<?php echo JText::_('COM_EASYSOCIAL_NOTIFICATIONS_MARK_UNREAD');?>">
					<i class="fa fa-eye"></i>
				</a>

				<a class="btn btn-es-danger-o btn-sm" href="javascript:void(0);" data-delete data-es-provide="tooltip" data-original-title="<?php echo JText::_('COM_EASYSOCIAL_NOTIFICATIONS_DELETE_ITEM');?>">
					<i class="fa fa-times"></i>
				</a>
			</div>
		</div>
		<?php } ?>
	</div>
	<?php } ?>
<?php } ?>
