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
<div class="es-stream-market-item">
	<div class="es-stream-market-item__media">
		<div class="es-stream-market-item__label">
			<i class="fa fa-shopping-bag"></i>
		</div>
		<div class="es-stream-market-item__img" style="background-image: url(<?php echo $photo; ?>);">
		</div>
	</div>

	<div class="es-stream-market-item__context">
		<a href="<?php echo ESR::marketplaces(); ?>" class="es-stream-market-item__cat">
			<?php echo JText::_('COM_ES_MARKETPLACE'); ?>
		</a>
		<a href="<?php echo $listing->getPermalink(); ?>" class="es-stream-market-item__title">
			<?php echo $listing->getTitle(); ?>
		</a>
		<ul class="g-list-inline g-list-inline--delimited es-stream-market-item__meta">
			<?php if ($listing->isSold()) { ?>
				<li>
					<?php echo JText::_('COM_ES_MARKETPLACES_SOLD'); ?></a>
				</li>
			<?php } ?>

			<li data-breadcrumb="·">
				<?php echo $listing->getPriceTag(); ?>
			</li>
			<li data-breadcrumb="·">
				<a href="<?php echo $listing->getCategory()->getPermalink(); ?>"><?php echo $listing->getCategory()->getTitle(); ?></a>
			</li>
		</ul>
		<div class="es-stream-market-item__desc">
			<?php echo $this->html('string.truncate', $listing->getDescription(), $this->config->get('stream.content.truncatelength'));?>
		</div>
	</div>
</div>
