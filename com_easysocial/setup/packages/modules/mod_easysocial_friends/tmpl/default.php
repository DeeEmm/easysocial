<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2016 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div id="es" class="mod-es mod-es-friends <?php echo $lib->getSuffix();?>">
	<ul class="g-list-inline">
		<?php foreach ($friends as $user) { ?>
		<li class="t-lg-mr--md t-lg-mb--md">
			<?php echo $lib->html('avatar.user', $user, 'default', $params->get('popover', true), $params->get('online_state', true), $params->get('popover_position', 'top-left')); ?>
		</li>
		<?php } ?>
	</ul>

	<?php if ($params->get('showall_link', true)) { ?>
	<div>
		<a href="<?php echo ESR::friends();?>" class="btn btn-es-default-o btn-sm btn-block"><?php echo JText::_('MOD_EASYSOCIAL_FRIENDS_VIEW_ALL'); ?></a>
	</div>
	<?php } ?>
</div>
