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
<div id="es" class="mod-es mod-es-menu <?php echo $lib->getSuffix();?>">

	<?php if ($showToolbar) { ?>
	<div class="mod-es-menu-bar">
		<nav class="o-nav">
			<?php if ($params->get('show_friends_notifications', true) && $lib->config->get('friends.enabled', true)) { ?>
			<div class="o-nav__item">
				<a class="o-nav__link mod-es-menu-bar__icon-link <?php echo $my->getTotalFriendRequests() > 0 ? 'has-new' : '';?>" href="javascript:void(0);"
					data-es-provide="tooltip" data-original-title="<?php echo JText::_('MOD_EASYSOCIAL_MENU_FRIEND_REQUESTS', true);?>" data-placement="top"
					data-notifications data-type="friends"
					data-popbox="module://easysocial/friends/popbox"
					data-popbox-toggle="click"
					data-popbox-type="navbar-friends"
					data-popbox-component="popbox--navbar"
					data-popbox-offset="4"
					data-popbox-position="<?php echo $params->get('popbox_position', 'bottom'); ?>"
					data-popbox-collision="<?php echo $params->get('popbox_collision', 'flip'); ?>"
				>
					<i class="fa fa-users"></i>
					<span data-counter="" class="mod-es-menu-bar__link-bubble"><?php echo $my->getTotalFriendRequests();?></span>
				</a>
			</div>
			<?php } ?>

			<?php if ($params->get('show_conversation_notifications', true) && $lib->config->get('conversations.enabled', true)) { ?>
			<div class="o-nav__item">
				<a class="o-nav__link mod-es-menu-bar__icon-link <?php echo $my->getTotalNewConversations() > 0 ? 'has-new' : '';?>" href="javascript:void(0);"
					data-es-provide="tooltip" data-original-title="<?php echo JText::_('MOD_EASYSOCIAL_MENU_CONVERSATIONS', true);?>" data-placement="top"
					data-notifications data-type="conversations"
					data-popbox="module://easysocial/conversations/popbox"
					data-popbox-toggle="click"
					data-popbox-type="navbar-conversations"
					data-popbox-component="popbox--navbar"
					data-popbox-offset="4"
					data-popbox-position="<?php echo $params->get('popbox_position', 'bottom'); ?>"
					data-popbox-collision="<?php echo $params->get('popbox_collision', 'flip'); ?>"
				>
					<i class="fa fa-envelope"></i>
					<span data-counter="" class="mod-es-menu-bar__link-bubble"><?php echo $my->getTotalNewConversations();?></span>
				</a>
			</div>
			<?php } ?>

			<?php if ($params->get('show_system_notifications', true)) { ?>
			<div class="o-nav__item">
				<a class="o-nav__link mod-es-menu-bar__icon-link <?php echo $my->getTotalNewNotifications() > 0 ? 'has-new' : '';?>" href="javascript:void(0);"
					data-es-provide="tooltip" data-original-title="<?php echo JText::_('MOD_EASYSOCIAL_MENU_NOTIFICATIONS', true);?>" data-placement="top"
					data-notifications data-type="notifications"
					data-popbox="module://easysocial/notifications/popbox"
					data-popbox-toggle="click"
					data-popbox-type="navbar-notifications"
					data-popbox-component="popbox--navbar"
					data-popbox-offset="4"
					data-popbox-position="<?php echo $params->get('popbox_position', 'bottom'); ?>"
					data-popbox-collision="<?php echo $params->get('popbox_collision', 'flip'); ?>"
					data-autoread="<?php echo $params->get('interval_notifications_system', 60 );?>"
					data-user-id="<?php echo $my->id;?>"
				>
					<i class="fa fa-bell"></i>
					<span data-counter="" class="mod-es-menu-bar__link-bubble"><?php echo $my->getTotalNewNotifications();?></span>
				</a>
			</div>
			<?php } ?>

			<?php if ($params->get('show_edit', true)) { ?>
			<div class="o-nav__item pull-right">
				<a class="o-nav__link mod-es-menu-bar__icon-link has-new" href="<?php echo ESR::profile(array('layout' => 'edit'));?>">
					<i class="fa fa-edit"></i>
				</a>
			</div>
			<?php } ?>
		</nav>
	</div>
	<?php } ?>

	<div class="mod-es-pf-hd">
		<div class="mod-es-pf-hd__cover-wrap">
			<div class="mod-es-pf-hd__cover" style="
				background-image : url(<?php echo $my->getCover(); ?>);
				background-position: <?php echo $my->getCoverPosition(); ?>;">
			</div>
		</div>
		<div class="mod-es-pf-hd__content">
			<div class="mod-es-pf-hd__avatar">
				<?php echo ES::themes()->html('avatar.user', $my, 'lg'); ?>
			</div>

			<a href="<?php echo $my->getPermalink(); ?>" class="mod-es-title"><?php echo $my->getName();?></a>

			<?php if ($params->get('show_points', true)) { ?>
			<div>
				<a class="mod-es-meta" href="<?php echo ESR::points(array('layout' => 'history' , 'userid' => $my->getAlias()));?>">
					<?php echo $my->getPoints(); ?> <?php echo JText::_('MOD_EASYSOCIAL_MENU_POINTS'); ?>
				</a>
			</div>
			<?php } ?>

			<?php if ($params->get('show_achievements', true) && $my->getBadges()) { ?>
			<div class="mod-es-pf-hd__badges">
				<?php foreach ($my->getBadges() as $badge) { ?>
				<a href="<?php echo $badge->getPermalink(); ?>" data-es-provide="tooltip" data-placement="top" data-original-title="<?php echo $lib->html('string.escape', $badge->_('title'));?>">
					<img alt="<?php echo $lib->html('string.escape', $badge->_('title'));?>" src="<?php echo $badge->getAvatar();?>">
				</a>
				<?php } ?>
			</div>
			<?php } ?>
		</div>
	</div>
	<?php if ($params->get('show_navigation', true)) { ?>
	<div class="mod-es-menu-list">
		<?php if ($params->get('show_conversation', true) && $lib->config->get('conversations.enabled', true)) { ?>
			<a href="<?php echo ESR::conversations();?>" class="mod-es-menu-list__item"><?php echo JText::_('MOD_EASYSOCIAL_MENU_CONVERSATIONS'); ?></a>
		<?php } ?>
		<?php if ($params->get('show_friends', true) && $lib->config->get('friends.enabled', true)) { ?>
			<a href="<?php echo ESR::friends();?>" class="mod-es-menu-list__item"><?php echo JText::_('MOD_EASYSOCIAL_MENU_FRIENDS'); ?></a>
		<?php } ?>

		<?php if ($params->get('show_followers', true) && $lib->config->get('followers.enabled', true)) { ?>
			<a href="<?php echo ESR::followers(); ?>" class="mod-es-menu-list__item"><?php echo JText::_('MOD_EASYSOCIAL_MENU_FOLLOWERS'); ?></a>
		<?php } ?>

		<?php if ($params->get('show_photos', true) && $lib->config->get('photos.enabled', true)) { ?>
			<a href="<?php echo ESR::albums(array('uid' => $my->getAlias(), 'type' => SOCIAL_TYPE_USER)); ?>" class="mod-es-menu-list__item"><?php echo JText::_('MOD_EASYSOCIAL_MENU_PHOTOS'); ?></a>
		<?php } ?>

		<?php if ($params->get('show_mygroups', true) && $lib->config->get('groups.enabled', true)) { ?>
			<a href="<?php echo ESR::groups(array('filter' => 'mine'));?>" class="mod-es-menu-list__item"><?php echo JText::_('MOD_EASYSOCIAL_MENU_MY_GROUPS'); ?></a>
		<?php } ?>

		<?php if ($params->get('show_myevents', true) && $lib->config->get('events.enabled', true)) { ?>
			<a href="<?php echo ESR::events(array('filter' => 'mine')); ?>" class="mod-es-menu-list__item"><?php echo JText::_('MOD_EASYSOCIAL_MENU_MY_EVENTS'); ?></a>
		<?php } ?>

		<?php if ($params->get('show_mypages', true) && $lib->config->get('pages.enabled', true)) { ?>
			<a href="<?php echo ESR::pages(array('filter' => 'mine'));?>" class="mod-es-menu-list__item"><?php echo JText::_('MOD_EASYSOCIAL_MENU_MY_PAGES'); ?></a>
		<?php } ?>

		<?php if ($params->get('show_mymarketplaces', true) && $lib->config->get('marketplaces.enabled', true)) { ?>
			<a href="<?php echo ESR::marketplaces(array('filter' => 'created')); ?>" class="mod-es-menu-list__item"><?php echo JText::_('MOD_EASYSOCIAL_MENU_MY_MARKETPLACES'); ?></a>
		<?php } ?>

		<?php if ($params->get('show_videos', true) && $lib->config->get('video.enabled', true)) { ?>
			<a href="<?php echo ESR::videos(array('filter' => 'mine')); ?>" class="mod-es-menu-list__item"><?php echo JText::_('MOD_EASYSOCIAL_MENU_VIDEOS'); ?></a>
		<?php } ?>

		<?php if ($params->get('show_audio', true) && $lib->config->get('audio.enabled', true)) { ?>
			<a href="<?php echo ESR::audios(array('filter' => 'mine')); ?>" class="mod-es-menu-list__item"><?php echo JText::_('MOD_ES_MENU_AUDIOS'); ?></a>
		<?php } ?>

		<?php if ($params->get('show_polls', true) && $lib->config->get('polls.enabled', true)) { ?>
			<a href="<?php echo ESR::polls(array('filter' => 'mine'));?>" class="mod-es-menu-list__item"><?php echo JText::_('MOD_EASYSOCIAL_MENU_POLLS'); ?></a>
		<?php } ?>

		<?php if ($params->get('show_apps', true) && $lib->config->get('apps.browser', true)) { ?>
			<a href="<?php echo ESR::apps();?>" class="mod-es-menu-list__item"><?php echo JText::_('MOD_EASYSOCIAL_MENU_APPS'); ?></a>
		<?php } ?>

		<?php if ($params->get('show_activity', true) && $lib->config->get('activity.logs.enabled', true)) { ?>
			<a href="<?php echo ESR::activities();?>" class="mod-es-menu-list__item"><?php echo JText::_('MOD_EASYSOCIAL_MENU_ACTIVITY_LOG'); ?></a>
		<?php } ?>

		<?php if ($params->get('integrate_easyblog', true) && $lib->requireEasyBlog()) { ?>
			<?php if (EB::acl()->get('add_entry')) { ?>
				<a class="mod-es-menu-list__item"
					href="<?php echo EB::composer()->getComposeUrl();?>">
					<?php echo JText::_('MOD_EASYSOCIAL_MENU_EASYBLOG_WRITE_NEW');?>
				</a>

				<a class="mod-es-menu-list__item"
					href="<?php echo EBR::_('index.php?option=com_easyblog&view=dashboard&layout=entries&Itemid=' . EBR::getItemId('dashboard'));?>">
					<?php echo JText::_('MOD_EASYSOCIAL_MENU_EASYBLOG_POSTS');?>
				</a>
			<?php } ?>
		<?php } ?>

		<?php if ($params->get('integrate_easydiscuss', true) && $lib->requireEasyDiscuss()) { ?>
			<a class="mod-es-menu-list__item" href="<?php echo EDR::_('index.php?option=com_easydiscuss&view=mypost&Itemid=' . EDR::getItemId('mypost'));?>">
				<?php echo JText::_('MOD_ES_MENU_MY_FORUM_POSTS');?>
			</a>
		<?php } ?>

	</div>
	<?php } ?>
	<?php if ($params->get('show_signout', true)) { ?>
	<div class="mod-es-menu-list">
		<form action="<?php echo JRoute::_('index.php');?>" method="post" id="es-mod-login-signout" data-es-menu-signout-form>
			<a href="javascript:void(0);" onclick="document.getElementById('es-mod-login-signout').submit();" data-es-menu-signout class="mod-es-menu-list__item">
				<?php echo JText::_('MOD_EASYSOCIAL_MENU_SIGN_OUT'); ?>
			</a>

			<input type="hidden" name="return" value="<?php echo $logoutReturn;?>" />
			<input type="hidden" name="option" value="com_easysocial" />
			<input type="hidden" name="controller" value="account" />
			<input type="hidden" name="task" value="logout" />
			<?php echo $lib->html('form.token'); ?>
		</form>
	</div>
	<?php } ?>
</div>
