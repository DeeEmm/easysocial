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
<div class="es-comment-item-meta__item t-lg-pr--no <?php echo $hasReaction < 1 ? ' t-hidden' : '';?>"
	data-likes-content="<?php echo $element . '-' . $group . '-' . $uid; ?>"
	data-id="<?php echo $uid;?>"
	data-likes-type="<?php echo $element;?>"
	data-group="<?php echo $group;?>"
	data-verb="<?php echo $verb;?>"
>
	<?php if ($this->isMobile()) { ?>
	<div class="es-comment-item-reaction-stats">
		<a href="javascript:void(0);"
			data-reaction-item="<?php echo $reactionKey; ?>"
			data-popbox="module://easysocial/likes/popbox"
			data-popbox-id="es"
			data-popbox-toggle="click"
			data-popbox-type="reaction"
			data-popbox-component="popbox-reaction-stats"
			data-popbox-offset="8"
			data-popbox-offset-horizontal="-16"
			data-popbox-position="top-center"
			data-popbox-collision="none"
			data-reaction-label
			rel="nofollow"
		>
			<?php echo JText::sprintf(ES::string()->computeNoun('COM_ES_TOTAL_REACTIONS', $totalReactions), $totalReactions); ?>
		</a>
	</div>
	<?php } else { ?>

	<div class="es-comment-item-reaction-stats">
		<div class="es-reaction-icon-stat">
			<?php foreach ($reactions as $reaction) { ?>
			<div class="es-reaction-icon-stat__item <?php echo $reaction->getTotal() < 1 ? 't-hidden' : '';?>" data-reaction-item="<?php echo $reaction->getKey();?>" data-count="<?php echo $reaction->getTotal();?>"
				data-popbox="module://easysocial/likes/popbox"
				data-popbox-id="es"
				data-popbox-toggle="click"
				data-popbox-type="reaction"
				data-popbox-component="popbox-reaction-stats"
				data-popbox-offset="8"
				data-popbox-offset-horizontal="-24"
				data-popbox-position="top-center"
				data-popbox-collision="none"
			>
				<div>
					<div class="es-icon-reaction es-icon-reaction--sm es-icon-reaction--<?php echo $reaction->getKey();?>"></div>
				</div>
				<div class="es-reaction-icon-stat__counter" data-reaction-counter="<?php echo $reaction->getKey();?>"><?php echo $reaction->getTotal();?></div>
			</div>
			<?php } ?>
		</div>
	</div>
	<?php } ?>

</div>
