<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<?php if (!$this->isMobile()) { ?>
<div class="t-lg-mb--md">
	<?php echo $this->html('html.miniheader', $user); ?>
</div>
<?php } ?>

<div class="es-container">
	<div class="es-content">
		
		<div class="es-stage es-island" data-points-history>

			<div class="es-stage__curtain es-bleed--top">

				<h3 class="es-stage__title">
					 <?php echo JText::_('COM_EASYSOCIAL_POINTS_HISTORY_TITLE');?>
				</h3>
				<div class="es-stage__desc">
					 <?php if ($this->my->id == $user->id) { ?>
						<?php echo JText::sprintf('COM_EASYSOCIAL_POINTS_HISTORY_YOURS_DESC', $user->getPoints()); ?>
						<a href="<?php echo ESR::points();?>"><?php echo JText::_('COM_EASYSOCIAL_EARN_MORE_POINTS');?></a>
					 <?php } else { ?>
						<?php echo JText::sprintf('COM_EASYSOCIAL_POINTS_HISTORY_DESC', $user->getName(), $user->getPoints()); ?>
					 <?php } ?>
				</div>
				<div class="es-stage__actor">
					<?php echo $this->html('avatar.user', $user, 'xl', false, false, '', false); ?>					
				</div>

			</div>

			<div class="es-stage__audience">

				<div class="es-stage__audience-result">
					<div class="es-pointshistory">
						<ul class="es-timeline" data-timeline>
							<?php echo $this->includeTemplate('site/points/history/item'); ?>
						</ul>

						<?php if ($histories) { ?>
							<?php if ($pagination->total > count($histories)) { ?>
							<button class="btn btn-es-default-o <?php echo $this->isMobile() ? 'btn-block' : '';?> btn-loadmore"<?php echo $pagination->total <= count($histories) ? ' disabled="disabled"' : '';?> data-pagination="<?php echo $pagination->pagesCurrent * $pagination->limit;?>">
								<?php echo JText::_('COM_EASYSOCIAL_LOAD_MORE');?>
							</button>
							<?php } ?>
						<?php } ?>
					</div>
				</div>

			</div>
		</div>

	</div>
</div>

