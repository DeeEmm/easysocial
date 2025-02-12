<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2016 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div id="es" class="mod-es mod-es-recentpolls">
	<?php if ($polls) { ?>
	<div class="o-box">
		<?php foreach ($polls as $poll) { ?>
		<div class="es-polls">
			<div class="o-flag t-lg-mb--lg">
				<?php if ($params->get('display_author', true)) { ?>
				<div class="o-flag__image o-flag--top">
					<a href="<?php echo $poll->getAuthor()->getPermalink(); ?>">
						<?php echo $lib->html('avatar.user', $poll->getAuthor()); ?>
					</a>
				</div>
				<?php } ?>
				<div class="o-flag__body">
					<a href="<?php echo $poll->getPermalink(); ?>">
						<b><?php echo $poll->title; ?></b>
					</a>
				</div>
			</div>

			<?php if ($params->get('display_pollitems', true)) { ?>
			<div class="es-polls__list ">
				<?php foreach ($poll->getItems() as $item) { ?>
				<div class="es-polls__item">
					<div>
						<?php echo $item->value;?>
						<div class="es-polls__progress progress">
							<div data-progress style="width: <?php echo $item->percentage; ?>%;" class="progress-bar progress-bar-primary"></div>
						</div>
						<a data-view-voters class="es-polls__count" href="javascript:void(0);">
							<span data-counter><?php echo $item->count; ?></span> <?php echo JText::_('COM_EASYSOCIAL_POLLS_VOTES_COUNT');?>
						</a>
					</div>
				</div>
				<?php } ?>
			</div>
			<?php } ?>
		</div>
		<?php } ?>
	</div>
	<?php } ?>
	<div class="mod-es-action">
		<a href="<?php echo ESR::polls();?>" class="btn btn-es-default-o btn-sm btn-block"><?php echo JText::_('MOD_EASYSOCIAL_RECENTPOLLS_VIEW_ALL_POLLS'); ?></a>
	</div>
</div>
