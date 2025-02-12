<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<tr class="<?php echo ($this->my->id && $user->id == $this->my->id) ? 't-bg--200': ''; ?>">
	<td>
		<div class="es-leader-badge es-leader-badge--<?php echo $pos;?>">
			<span><?php echo $pos;?></span>
		</div>
	</td>
	<td>
		<div class="o-flag" >
			<div class="o-flag__image">
				<?php echo $this->html('avatar.user', $user); ?>
			</div>
			<div class="o-flag__body">
				<?php echo $this->html('html.user', $user); ?>
			</div>
		</div>
	</td>
	<td>
		<span class="es-leaderboard__points"><?php echo $user->getPoints();?></span>
		<span><?php echo JText::_('COM_EASYSOCIAL_POINTS');?></span>
	</td>
</tr>