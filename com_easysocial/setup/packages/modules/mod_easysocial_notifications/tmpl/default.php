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
<script>
EasySocial.require()
.script('site/system/notifications', 'site/system/keepalive')
.done(function($) {

	$('body').implement(EasySocial.Controller.System.Notifications, {
		"interval": <?php echo $lib->config->get('notifications.polling.interval');?>,
		"userId": "<?php echo $my->id;?>",
		"type": "<?php echo $lib->config->get('notifications.polling.single') ? 'single' : 'multiple';?>"
	});

<?php if (!$isEasySocialPages && ES::keepAlive()) { ?>
	if (window.es.mobile || window.es.tablet) {
		$('div.mod-es-notification').implement(EasySocial.Controller.System.KeepAlive);
	}
<?php } ?>
});
</script>

<div id="es" class="mod-es mod-es-notification <?php echo $lib->getSuffix();?>">
	<div class="mod-es-menu-bar">
		<nav class="o-nav">
			<?php if ($params->get('show_friends_notifications', true) && $friendsEnabled) { ?>
			<div class="o-nav__item">
				<a class="o-nav__link mod-es-menu-bar__icon-link <?php echo $my->getTotalFriendRequests() > 0 ? 'has-new' : '';?>" href="javascript:void(0);"
					data-es-provide="tooltip" data-original-title="<?php echo JText::_('MOD_EASYSOCIAL_NOTIFICATIONS_FRIEND_REQUESTS', true);?>" data-placement="<?php echo $params->get('tooltip_position', 'top') ?>"
					data-notifications data-type="friends"
					data-popbox="module://easysocial/friends/popbox"
					data-popbox-toggle="click"
					data-popbox-type="navbar-friends"
					data-popbox-component="popbox--navbar"
					data-popbox-offset="<?php echo $params->get('popbox_offset', 10); ?>"
					data-popbox-position="<?php echo $params->get('popbox_position', 'bottom'); ?>"
					data-popbox-collision="<?php echo $params->get('popbox_collision', 'flip'); ?>"
				>
					<i class="fa fa-user-friends"></i>
					<span data-counter class="mod-es-menu-bar__link-bubble"><?php echo $my->getTotalFriendRequests();?></span>
				</a>
			</div>
			<?php } ?>

			<?php if ($params->get('show_conversation_notifications', true) && $lib->config->get('conversations.enabled', true)) { ?>
			<div class="o-nav__item">
				<a class="o-nav__link mod-es-menu-bar__icon-link <?php echo $my->getTotalNewConversations() > 0 ? 'has-new' : '';?>" href="javascript:void(0);"
					data-es-provide="tooltip" data-original-title="<?php echo JText::_('MOD_EASYSOCIAL_NOTIFICATIONS_CONVERSATIONS', true);?>" data-placement="<?php echo $params->get('tooltip_position', 'top') ?>"
					data-notifications data-type="conversations"
					data-popbox="module://easysocial/conversations/popbox"
					data-popbox-toggle="click"
					data-popbox-type="navbar-conversations"
					data-popbox-component="popbox--navbar"
					data-popbox-offset="<?php echo $params->get('popbox_offset', 10); ?>"
					data-popbox-position="<?php echo $params->get('popbox_position', 'bottom'); ?>"
					data-popbox-collision="<?php echo $params->get('popbox_collision', 'flip'); ?>"
					data-popbox-view="<?php echo $view; ?>"
				>
					<i class="fa fa-envelope"></i>
					<span data-counter class="mod-es-menu-bar__link-bubble"><?php echo $my->getTotalNewConversations();?></span>
				</a>
			</div>
			<?php } ?>

			<?php if ($params->get('show_system_notifications', true)) { ?>
			<div class="o-nav__item">
				<a class="o-nav__link mod-es-menu-bar__icon-link <?php echo $my->getTotalNewNotifications() > 0 ? 'has-new' : '';?>" href="javascript:void(0);"
					data-es-provide="tooltip" data-original-title="<?php echo JText::_('MOD_EASYSOCIAL_NOTIFICATIONS_NOTIFICATIONS', true);?>" data-placement="<?php echo $params->get('tooltip_position', 'top') ?>"
					data-notifications data-type="notifications"
					data-popbox="module://easysocial/notifications/popbox"
					data-popbox-toggle="click"
					data-popbox-type="navbar-notifications"
					data-popbox-component="popbox--navbar"
					data-popbox-offset="<?php echo $params->get('popbox_offset', 10); ?>"
					data-popbox-position="<?php echo $params->get('popbox_position', 'bottom'); ?>"
					data-popbox-collision="<?php echo $params->get('popbox_collision', 'flip'); ?>"
					data-autoread="<?php echo $params->get('interval_notifications_system', 60 );?>"
					data-user-id="<?php echo $my->id;?>"
				>
					<i class="fa fa-bell"></i>
					<span data-counter class="mod-es-menu-bar__link-bubble"><?php echo $my->getTotalNewNotifications();?></span>
				</a>
			</div>
			<?php } ?>
		</nav>
	</div>
</div>
