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
<div class="es-reaction-stats <?php echo $hasReaction < 1 ? ' t-hidden' : '';?>"
	data-likes-content="<?php echo $element . '-' . $group . '-' . $uid; ?>"
	data-id="<?php echo $uid;?>"
	data-likes-type="<?php echo $element;?>"
	data-group="<?php echo $group;?>"
	data-verb="<?php echo $verb;?>"
	data-clusterid="<?php echo $clusterId;?>"
>
	<div class="es-reaction-stats__icons">
		<div class="es-reaction-icon-stat">
			<?php foreach ($reactions as $reaction) { ?>
			<div class="es-reaction-icon-stat__item <?php echo $reaction->getTotal() < 1 ? 't-hidden' : '';?>"
				data-reaction-item="<?php echo $reaction->getKey();?>"
				data-count="<?php echo $reaction->getTotal();?>"
				data-popbox="module://easysocial/likes/popbox"
				data-popbox-id="es"
				data-popbox-toggle="click"
				data-popbox-type="reaction"
				data-popbox-component="popbox-reaction-stats"
				data-popbox-offset="8"
				data-popbox-position="top-left"
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

	<?php if (!$this->isMobile()) { ?>
	<div class="es-reaction-stats__text">
		<a href="javascript:void(0);"
			data-popbox="module://easysocial/likes/popbox"
			data-popbox-id="es"
			data-popbox-toggle="click"
			data-popbox-type="reaction"
			data-popbox-component="popbox-reaction-stats"
			data-popbox-offset="8"
			data-popbox-position="top-right"
			data-popbox-collision="none"
			rel="nofollow"
		>
			<span data-reaction-label><?php echo $text;?></span>
		</a>

		<div class="t-hidden" data-es-reaction-stats>
			<div class="es-reaction-stats-pop t-hidden">
			</div>
		</div>
	</div>
	<?php } ?>
</div>
