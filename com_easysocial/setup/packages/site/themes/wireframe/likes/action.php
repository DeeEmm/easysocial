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
<div class="es-reaction-option"
	data-es-likes-container
	data-id="<?php echo $uid;?>"
	data-likes-type="<?php echo $element;?>"
	data-group="<?php echo $group;?>"
	data-verb="<?php echo $verb;?>"
	data-streamid="<?php echo $streamid;?>"
	data-clusterid="<?php echo empty($clusterId) ? '' : $clusterId; ?>"
	data-current="<?php echo $selectedReaction ? $selectedReaction->getKey() : '';?>"
	data-default="<?php echo $baseReaction->getKey();?>"
	data-default-text="<?php echo $baseReaction->getText();?>"
	data-uri="<?php echo base64_encode(ES::getURI(true));?>"
>
	<a href="javascript:void(0);"
		class="es-reaction-option__link <?php echo $selectedReaction ? 'is-active' : '';?> <?php echo $buttonStyle ? 'btn btn-es-primary-o btn-rounded' : '';?>"
		data-es-likes="<?php echo $selectedReaction ? $selectedReaction->getKey() : $baseReaction->getKey();?>"
		data-button-main
	>
		<div class="es-reaction-option__text">
			<?php if ($buttonStyle) { ?>
				<div class="es-icon-reaction es-icon-reaction--sm es-icon-reaction--<?php echo $selectedReaction ? $selectedReaction->getKey() : $baseReaction->getKey();?>" data-button-main-icon></div>
			<?php } ?>

			<span data-button-text>
			<?php if ($selectedReaction) { ?>
				<?php echo $selectedReaction->getText();?>
			<?php } else { ?>
				<?php echo $baseReaction->getText(); ?>
			<?php } ?>
			</span>
		</div>
	</a>

	<?php if (count($reactions) > 1) { ?>
	<div class="es-reactions-pop" data-reactions-list>
		<?php foreach ($reactions as $reaction) { ?>
		<div class="es-reactions-pop__item <?php echo $selectedReaction && $reaction->getKey() == $selectedReaction->getKey() ? ' is-active' : '';?>"
			data-es-likes="<?php echo $reaction->getKey();?>"
			data-text="<?php echo $reaction->getText();?>"
		>
			<div class="es-reactions-pop__text">
				<?php echo $reaction->getText();?>
			</div>
			<div class="es-icon-reaction es-icon-reaction--md es-icon-reaction--<?php echo $reaction->getKey();?>"></div>
		</div>
		<?php } ?>
	</div>
	<?php } ?>
</div>
