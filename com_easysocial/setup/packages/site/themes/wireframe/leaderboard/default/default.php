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
<div class="es-container">
	<div class="es-content" data-leadearboard>
		<div class="es-stage es-island">
			<?php if (!$this->my->guest) { ?>
			<div class="es-stage__curtain es-bleed--top t-lg-mb--no">
				<h3 class="es-stage__title">
					<?php echo JText::_('COM_EASYSOCIAL_POINTS_LEADERBOARD');?>
				</h3>
				<div class="es-stage__desc">
					<label><?php echo JText::_('COM_ES_POINTS_LEADERBOARD_YOUR_RANK'); ?><?php echo $userRank->pos; ?></label>
					<?php echo JText::sprintf('COM_ES_POINTS_LEADERBOARD_POINTS', $userRank->points); ?>
				</div>
				<div class="es-stage__actor">
					<?php echo $this->html('avatar.user', $this->my, 'xl', false, false, '', false); ?>
				</div>
			</div>
			<?php } ?>

			<div class="es-stage__curtain es-stage__curtain--off">
				<?php if ($this->my->guest) { ?>
				<h3 class="es-stage__title">
					<?php echo JText::_('COM_EASYSOCIAL_POINTS_LEADERBOARD');?>
				</h3>
				<?php } ?>
				<div class="es-stage__desc">
					<?php echo JText::_('COM_EASYSOCIAL_POINTS_LEADERBOARD_DESC'); ?><br>
					<a href="<?php echo ESR::points();?>"><?php echo JText::_('COM_EASYSOCIAL_EARN_MORE_POINTS');?></a>
				</div>
			</div>

			<div class="es-stage__audience">

				<?php echo $this->render('module', 'es-leaderboard-before-contents'); ?>

				<div class="es-stage__audience-result">
					<table class="es-leaderboard">
						<thead>
							<tr>
								<th><?php echo JText::_('COM_EASYSOCIAL_TABLE_COLUMN_RANK');?></th>
								<th><?php echo JText::_('COM_EASYSOCIAL_TABLE_COLUMN_USER');?></th>
								<th><?php echo JText::_('COM_EASYSOCIAL_TABLE_COLUMN_POINTS');?></th>
							</tr>
						</thead>
						<tbody data-leaderboard-list>
							<?php $i = 1; ?>
							<?php foreach ($users as $user) { ?>
							<?php echo $this->loadTemplate('site/leaderboard/default/item', array('user' => $user, 'pos' => $i)); ?>
							<?php $i++; ?>
							<?php } ?>
						</tbody>
					</table>
				</div>
			</div>

			<div class="es-stage__audience t-lg-pt--md<?php echo $limitstart <= 0 ? ' t-hidden': ''; ?>">
				<div class="es-pagination">
					<a href="javascript:void(0);" class="btn btn-es-default-o btn-block" data-pagination data-limitstart="<?php echo $limitstart; ?>">
						<i class="fa fa-refresh"></i>&nbsp; <?php echo JText::_('COM_ES_LEADERBOARD_LOAD_MORE_BUTTON'); ?>
					</a>
				</div>
			</div>
		</div>

		<?php echo $this->render('module' , 'es-leaderboard-after-contents'); ?>
	</div>
</div>