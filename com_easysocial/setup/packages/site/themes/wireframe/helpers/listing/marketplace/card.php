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
<div class="es-cards__item" data-item data-id="<?php echo $listing->id;?>">
	<div class="es-card <?php echo $listing->isFeatured() ? ' is-featured' : '';?>">
		<div class="es-card__hd">
			<div class="es-card__action-group">
				<?php if ($listing->canAccessActionMenu()) { ?>
				<div class="es-card__admin-action">
					<div class="pull-right dropdown_">
						<a href="javascript:void(0);" class="btn btn-es-default-o btn-sm dropdown-toggle_" data-es-toggle="dropdown">
							<i class="fa fa-ellipsis-h"></i>
						</a>
						<ul class="dropdown-menu">
							<?php echo $this->html('marketplace.adminActions', $listing); ?>

							<?php $reportHtml = $this->html('marketplace.report', $listing); ?>

							<?php if ($reportHtml) { ?>
							<li>
								<?php echo $reportHtml; ?>
							</li>
							<?php } ?>
						</ul>
					</div>
				</div>
				<?php } ?>
			</div>

			<a href="<?php echo $listing->getPermalink();?>" class="embed-responsive embed-responsive-16by9">
				<div class="embed-responsive-item es-card__cover"
					style="
						background-image: url(<?php echo $listing->getSinglePhoto('large'); ?>);
						background-position: center center;"
				>
				</div>
			</a>
		</div>

		<div class="es-card__bd es-card--border">
			<div class="es-label-state es-label-state--featured es-card__state"><i class="es-label-state__icon"></i></div>
			<div class="es-card__title">
				<a href="<?php echo $listing->getPermalink(true, null, null, $from);?>"><?php echo $listing->getTitle();?></a>
			</div>

			<div class="es-card__title">
				<div class="t-d--flex t-align-items--c">
					<div>
						<div class="o-label2 o-label2--success"><?php echo $listing->getPriceTag(); ?></div>
					</div>
					&nbsp;
					<?php if ($listing->isSold()) { ?>
						<div>
							<div class="o-label2 o-label2--danger"><?php echo JText::_('COM_ES_MARKETPLACES_SOLD') ?></div>
						</div>
					<?php } else if ($listing->showStock()) { ?>
						<div>
							<?php if ($listing->isStockAvailable()) { ?>
								<div class="o-label2 o-label2--default"><?php echo JText::_('COM_ES_MARKETPLACES_IN_STOCK') ?></div>
							<?php } else { ?>
								<div class="o-label2 o-label2--danger"><?php echo JText::_('COM_ES_MARKETPLACES_OUT_OF_STOCK') ?></div>
							<?php } ?>
						</div>
					<?php } ?>
				</div>
			</div>

			<?php if ($browseView) { ?>
			<div class="es-card__meta t-lg-mb--sm">
				<ol class="g-list--horizontal">
					<li class="g-list__item"><?php echo JText::sprintf('COM_ES_MARKETPLACES_BY', $this->html('html.user', $listing->creator, true));?></li>
				</ol>
			</div>
			<?php } ?>

		</div>
		<div class="es-card__ft es-card--border">
			<ul class="g-list-flex">
				<li>
					<div><i class="fa fa-heart"></i> <?php echo $listing->getLikesCount();?></div>
				</li>
				<li>
					<div><i class="fa fa-comment"></i> <?php echo $listing->getCommentsCount();?></div>
				</li>
				<?php if ($this->config->get('marketplaces.layout.address') && !empty($listing->address)) { ?>
					<li>
						<a href="<?php echo $listing->getAddressLink(); ?>" target="_blank"><i class="fa fa-map-marker-alt"></i>&nbsp; <?php echo ESJString::substr($listing->address, 0, 15) . JText::_('COM_EASYSOCIAL_ELLIPSES'); ?></a>
					</li>
				<?php } ?>
			</ul>
		</div>
	</div>
</div>
