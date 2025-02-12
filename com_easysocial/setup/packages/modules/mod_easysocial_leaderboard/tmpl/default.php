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
<div id="es" class="mod-es mod-es-leader <?php echo $lib->getSuffix();?>">
	<?php $i = 1; ?>
	<?php foreach ($users as $user) { ?>
		<div class="mod-es-leader-item">
			<div class="es-leader-badge es-leader-badge--<?php echo $i;?>">
				<span><?php echo $i;?></span>
			</div>
			<div class="es-leader-context o-media o-media--top">
				<div class="o-media__image">
					<?php echo $lib->html('avatar.user', $user, 'default', $popover); ?>
				</div>
				<div class="o-media__body">
					<div class="es-leader-context__info">
						<?php echo $lib->html('html.user', $user, $popover);?>
						<div class="mod-es-leader__points"><?php echo JText::sprintf('MOD_EASYSOCIAL_LEADERBOARD_USER_POINTS', $user->getPoints());?></div>
					</div>
				</div>
			</div>
		</div>
		<?php $i++; ?>
	<?php } ?>

	<?php if ($params->get('showall_link', true)) { ?>
	<a href="<?php echo ESR::leaderboard();?>" class="t-lg-mt--xl btn btn-es-default-o btn-block btn-sm"><?php echo JText::_('MOD_EASYSOCIAL_LEADERBOARD_VIEW_LEADERBOARD'); ?></a>
	<?php } ?>
</div>
