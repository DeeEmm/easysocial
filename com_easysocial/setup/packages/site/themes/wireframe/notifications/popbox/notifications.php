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
<div class="popbox-dropdown" data-es-system-notifications>
	<div class="popbox-dropdown__hd">
		<div class="t-d--flex t-align-items--c">
			<div class="t-min-width--0 t-text--truncate t-pr--sm">
				<div class="popbox-dropdown__title t-text--truncate"><?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_NOTIFICATIONS'); ?></div>
			</div>
			<div class="t-ml--auto t-flex-shrink--0">
				<div class="">
					<a href="javascript:void(0);" class="popbox-dropdown__note" data-readall>
						<?php echo JText::_('COM_EASYSOCIAL_MARK_ALL_READ');?>
					</a>
				</div>
			</div>
		</div>
	</div>

	<div class="popbox-dropdown__bd">
		<div class="popbox-dropdown-nav" data-items>
		<?php if ($notifications) { ?>
			<?php foreach ($notifications as $item) { ?>
			<div class="popbox-dropdown-nav__item type-<?php echo $item->type;?> is-unread">
				<a href="<?php echo $item->getPermalink();?>" class="popbox-dropdown-nav__link">
					<div class="o-flag">
						<div class="o-flag__image o-flag--top">
							<?php if (isset($item->userOverride)) { ?>
								<?php echo $this->html('avatar.user', $item->userOverride, 'sm', false, false, '', false); ?>
							<?php } else if ($item->user->getType() == SOCIAL_TYPE_USER) { ?>
								<?php echo $this->html('avatar.user', $item->user, 'sm', false, false, '', false); ?>
							<?php } else { ?>
								<?php echo $this->html('avatar.cluster', $item->user, 'sm', false, false, '', false); ?>
							<?php } ?>
						</div>

						<div class="o-flag__body">
							<?php if ($item->image) { ?>
							<span class="popbox-dropdown-nav__image">
								<span style="background-image: url('<?php echo $item->image;?>');"></span>
							</span>
							<?php } ?>

							<div class="popbox-dropdown-nav__post">
								<?php echo JText::_($item->title); ?>
							</div>

							<?php if ($item->content) { ?>
							<div class="object-content">
								"<b><?php echo $item->content; ?></b>"
							</div>
							<?php } ?>

							<ol class="g-list-inline g-list-inline--delimited popbox-dropdown-nav__meta-lists">
								<?php if (isset($item->reaction) && !empty($item->reaction)) { ?>
									<li>
										<div class="es-reaction-list-wrapper">
											<?php foreach ($item->reaction as $reaction) { ?><div><i class="es-icon-reaction es-icon-reaction--sm es-icon-reaction--<?php echo $reaction; ?>"></i></div><?php } ?>
										</div>
									</li>
								<?php } ?>
								<?php if ($item->icon) { ?>
								<li>
									<i class="icon-es-games icon-tb-notice pull-left"></i>
								</li>
								<?php } else if ($item->type == 'broadcast') { ?>
								<li>
									<i class="fa fa-bullhorn"></i>
								</li>
								<?php } else { ?>
								<li>
									<i class="fa fa-globe-americas"></i>
								</li>
								<?php } ?>
								<li>
									<?php echo $item->since; ?>
								</li>
							</ol>
						</div>
					</div>
				</a>
			</div>
			<?php } ?>
		<?php } ?>
		</div>

		<div class="t-text--muted <?php echo $notifications ? '' : 'is-empty';?>" data-empty>
			<?php echo $this->html('html.emptyBlock', 'COM_EASYSOCIAL_NOTIFICATIONS_NO_UNREAD', 'fa-bell', false, false);?>
		</div>
	</div>
	<div class="popbox-dropdown__ft">
		<a href="<?php echo ESR::notifications();?>" class="popbox-dropdown__note">
			<?php echo JText::_('COM_ES_VIEW_ALL'); ?>
		</a>
	</div>
</div>
