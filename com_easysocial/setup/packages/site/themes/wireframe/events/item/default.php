<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div class="es-profile" data-es-event data-id="<?php echo $event->id;?>">

	<?php echo $this->html('cover.event', $event, $layout); ?>

	<div class="es-container" data-es-container>

		<?php echo $this->html('html.sidebar'); ?>

		<?php if ($this->isMobile()) { ?>
			<?php echo $this->output('site/events/item/mobile'); ?>
		<?php } ?>

		<div class="es-content">

			<div style=" display: none; position: fixed; background: #000; bottom: 10%; left: 40%; color: #fff; padding: 5px 10px; border-radius: 20px; opacity: 0.9;" data-copied-stream-item-message>
				<?php echo JText::_('COM_ES_STREAM_ITEM_LINK_COPIED_MESSAGE');?>
			</div>

			<?php echo $this->render('module', 'es-event-before-contents'); ?>

			<div class="es-stream-filters">
				<?php echo $streamFilter->html();?>
			</div>

			<div class="es-content-wrap" data-wrapper>
				<?php echo $this->html('listing.loader', 'stream', 8); ?>

				<div data-contents data-stream-contents>
					<?php echo $this->includeTemplate('site/events/item/feeds'); ?>
				</div>
			</div>

			<?php echo $this->render('module', 'es-events-after-contents'); ?>
		</div>
	</div>
</div>
