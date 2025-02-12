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
<div class="es-streams <?php echo !is_array($streams) ? ' is-empty' : ''; ?>" data-es-streams data-cluster="<?php echo $cluster ? '1' : '0';?>" data-currentdate="<?php echo ES::date()->toSql(); ?>" data-excludeids data-identifier="<?php echo $identifier; ?>">

	<?php echo $this->render('module', 'es-' . $view . '-before-story'); ?>

	<?php if ($story) { ?>
		<?php echo $story->html(); ?>
	<?php } ?>

	<?php echo $this->render('module', 'es-' . $view . '-after-story'); ?>

	<?php echo $this->render('module', 'es-' . $view . '-before-stream'); ?>

	<div data-updates-bar></div>

	<div class="es-stream-pinned-divider <?php echo !$stickies ? 't-hidden' : ''; ?>" data-stream-sticky-divider><span><i class="fa fa-thumbtack"></i> <?php echo JText::_('COM_EASYSOCIAL_STREAM_PINNED_ITEMS'); ?></span></div>
	<ul class="es-stream-list is-sticky <?php echo !$stickies ? 't-hidden' : ''; ?>" data-stream-sticky-list>
	<?php if (isset($stickies) && $stickies && is_array($stickies)) { ?>
			<?php foreach ($stickies as $sticky) { ?>
				<?php echo $this->loadTemplate('site/stream/default/item', array('stream' => $sticky, 'showTranslations' => $showTranslations, 'view' => $view)); ?>
			<?php } ?>
	<?php } ?>
	</ul>


	<div class="es-stream-pinned-divider <?php echo !$stickies ? 't-hidden' : ''; ?>" data-stream-recent-divider><span> <?php echo JText::_('COM_EASYSOCIAL_STREAM_RECENT_ACTIVITIES'); ?></span></div>
	<ul class="es-stream-list" data-stream-list>
		<?php if ($streams && is_array($streams)) { ?>
			<?php $pos = 1; ?>
			<?php foreach ($streams as $stream) { ?>
				<?php echo ES::stream()->beforeDisplayStreamItem($stream, $pos, $view); ?>
				<?php if ($stream->getType() == SOCIAL_TYPE_ADVERTISEMENT) { ?>
					<?php echo $stream->html(); ?>
				<?php } else { ?>
					<?php echo $this->loadTemplate('site/stream/default/item' , array('stream' => $stream, 'showTranslations' => $showTranslations, 'view' => $view)); ?>
				<?php } ?>
				<?php echo ES::stream()->afterDisplayStreamItem($stream, $pos++, $view); ?>
			<?php } ?>
		<?php } ?>
	</ul>

	<?php echo $this->html('html.emptyBlock', $empty, 'fa-bullseye'); ?>

	<?php if (!$this->my->guest) { ?>
	<div class="es-pagination">
		<?php if ($nextlimit) { ?>
		<a href="javascript:void(0);" class="btn btn-es-default-o btn-block" data-pagination data-customlimit="<?php echo $customlimit;?>" data-nextlimit="<?php echo $nextlimit;?>"
			data-nextdatelimit="<?php echo $nextdatelimit;?>"
			data-context="<?php echo $this->html('string.escape', $context);?>" data-exclude-streamids="<?php echo $this->html('string.escape', ES::json()->encode($excludeStreamIds)); ?>">
			<i class="fa fa-refresh"></i>&nbsp; <?php echo JText::_('COM_EASYSOCIAL_STREAM_LOAD_PREVIOUS_STREAM_ITEMS'); ?>
		</a>
		<?php } ?>
	</div>
	<?php } ?>

	<?php if ($this->my->guest) { ?>
		<?php echo $this->includeTemplate('site/dashboard/guests/stream.login'); ?>
	<?php } ?>

	<div class="es-view-after-stream">
		<?php echo $this->render('module', 'es-' . $view . '-after-stream'); ?>
	</div>
</div>
