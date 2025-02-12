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
<div class="es-cards__item" data-listing-item data-id="<?php echo $listing->id;?>">
	<div class="es-card <?php echo ($listing->table->isFeatured()) ? 'is-featured' : ''; ?>">
		<div class="es-card__hd">
			<div class="es-card__action-group">
				<?php if ($listing->canFeature() || $listing->canUnfeature() || $listing->canDelete() || $listing->canEdit()) { ?>
				<div class="es-card__admin-action">
					<div class="pull-right dropdown_">
						<a href="javascript:void(0);" class="btn btn-es-default-o btn-sm dropdown-toggle_" data-es-toggle="dropdown"><i class="fa fa-ellipsis-h"></i></a>
						<ul class="dropdown-menu">
							<?php if ($listing->canFeature()) { ?>
							<li>
								<a href="javascript:void(0);" data-listing-feature data-return="<?php echo $returnUrl;?>"><?php echo JText::_('COM_ES_MARKETPLACES_FEATURE_LISTING');?></a>
							</li>
							<?php } ?>

							<?php if ($listing->canUnfeature()) { ?>
							<li>
								<a href="javascript:void(0);" data-listing-unfeature data-return="<?php echo $returnUrl;?>"><?php echo JText::_('COM_ES_MARKETPLACES_UNFEATURE_LISTING');?></a>
							</li>
							<?php } ?>

							<?php if ($listing->canEdit()) { ?>
							<li>
								<a href="<?php echo $listing->getEditLink();?>"><?php echo JText::_('COM_ES_EDIT'); ?></a>
							</li>
							<?php } ?>

							<?php if ($listing->canDelete()) { ?>
							<li class="divider"></li>

							<li>
								<a href="javascript:void(0);" data-listing-delete data-return="<?php echo $returnUrl;?>"><?php echo JText::_('COM_ES_DELETE');?></a>
							</li>
							<?php } ?>
						</ul>
					</div>
				</div>
				<?php } ?>
			</div>
			<a href="<?php echo $listing->getPermalink(true, $listing->uid, $type, $from);?>" class="embed-responsive embed-responsive-16by9">
				<div class="embed-responsive-item es-card__cover"
					style="
						background-image: url(<?php echo $listing->getSinglePhoto(); ?>);
						background-position: center center;"
				>
				</div>
			</a>
		</div>
		<div class="es-card__bd es-card--border">
			<div class="es-label-state es-label-state--featured es-card__state"><i class="es-label-state__icon"></i></div>
			<div class="es-card__title">
				<a href="<?php echo $listing->getPermalink(true, $uid, $utype, $from);?>"><?php echo $listing->getTitle();?></a>
			</div>

			<div class="es-card__meta t-lg-mb--sm">
				<ol class="g-list--horizontal">
					<li class="g-list__item">
						<a href="<?php echo $listing->getCategory()->getPermalink(true, $uid, $utype);?>">
							<?php echo $listing->getPriceTag(); ?>
						</a>
					</li>

					<?php if ($browseView) { ?>
					<li class="t-lg-p--sm">&bull;</li>
					<li class="g-list__item">By <?php echo $this->html('html.user', $listing->creator, true);?></li>
					<?php } ?>
				</ol>
			</div>
		</div>
		<div class="es-card__ft es-card--border">
			<ul class="g-list-flex">
				<li>
					<div><i class="fa fa-heart"></i> <?php echo $listing->getLikesCount();?></div>
				</li>
				<li>
					<div><i class="fa fa-comment"></i> <?php echo $listing->getCommentsCount();?></div>
				</li>
			</ul>
		</div>
	</div>
</div>
