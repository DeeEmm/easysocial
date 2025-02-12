<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div class="es-profile" data-es-group data-id="<?php echo $group->id;?>">

	<?php echo $this->html('cover.group', $group, $layout); ?>

	<div class="es-container" data-es-container>

		<?php echo $this->html('html.sidebar'); ?>

		<?php if ($this->isMobile()) { ?>
			<?php echo $this->output('site/groups/item/mobile'); ?>
		<?php } ?>

		<div class="es-content">

			<div style=" display: none; position: fixed; background: #000; bottom: 10%; left: 40%; color: #fff; padding: 5px 10px; border-radius: 20px; opacity: 0.9;" data-copied-stream-item-message>
				<?php echo JText::_('COM_ES_STREAM_ITEM_LINK_COPIED_MESSAGE');?>
			</div>

			<?php echo $this->render('module', 'es-group-before-contents'); ?>

			<?php if ($layout != 'info') { ?>
			<div class="es-stream-filters">
				<?php echo $streamFilter->html();?>
			</div>

			<div class="es-content-wrap" data-wrapper>
				<?php echo $this->html('listing.loader', 'stream', 8); ?>

				<div data-contents data-stream-contents>
					<?php echo $this->output('site/groups/item/feeds', array('type' => $layout, 'customFilter' => $customFilter, 'stream' => $stream, 'group' => $group)); ?>
				</div>
			</div>
			<?php } ?>

			<?php if ($layout == 'info') { ?>
			<div class="es-profile-info">
				<?php if ($steps) { ?>
					<?php echo $this->output('site/fields/about/default', array('steps' => $steps, 'canEdit' => $group->isAdmin(), 'objectId' => $group->id, 'routerType' => 'groups', 'item' => $group)); ?>
				<?php } ?>
			</div>
			<?php } ?>

			<?php echo $this->render('module', 'es-groups-after-contents'); ?>
		</div>
	</div>
</div>
