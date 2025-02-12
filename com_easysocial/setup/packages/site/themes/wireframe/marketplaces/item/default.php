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
<div class="wrapper-for-full-height">
<?php echo $listing->getMiniHeader();?>

<div class="es-container es-marketplace" data-marketplace-item data-id="<?php echo $listing->id;?>">
	<div class="es-content">
		<!-- es-marketplaces-before-listing -->
		<?php echo $this->render('module' , 'es-marketplaces-before-listing'); ?>

		<div class="es-entry-actionbar es-island">
			<div class="o-grid-sm">
				<div class="o-grid-sm__cell o-grid-sm__cell--auto-size">
					<a href="<?php echo $backLink;?>" class="btn btn-es-default-o btn-sm">
						&larr; <?php echo $backLinkText; ?>
					</a>
				</div>

				<?php if ($listing->canFeature() || $listing->canUnfeature() || $listing->canDelete() || $listing->canEdit()) { ?>
				<div class="o-grid-sm__cell">
					<div class="o-btn-group pull-right" role="group">
						<button type="button" class="btn btn-es-default-o btn-sm dropdown-toggle_" data-es-toggle="dropdown">
							 <i class="fa fa-ellipsis-h"></i>
							 <span class="t-hidden"><?php echo JText::_('COM_EASYSOCIAL_MANAGE');?></span>
						</button>
						<ul class="dropdown-menu dropdown-menu-right">
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
								<a href="<?php echo $listing->getEditLink();?>"><?php echo JText::_('COM_EASYSOCIAL_EDIT'); ?></a>
							</li>
							<?php } ?>

							<?php if ($listing->canDelete()) { ?>
							<li>
								<a href="javascript:void(0);" data-listing-delete><?php echo JText::_('COM_EASYSOCIAL_DELETE');?></a>
							</li>
							<?php } ?>

							<?php if ($listing->canMarkAsSold()) { ?>
								<li>
									<a href="javascript:void(0);" data-listing-sold><?php echo JText::_('COM_ES_MARKETPLACES_MARK_SOLD_LISTING');?></a>
								</li>
							<?php } ?>

							<?php if ($listing->canMarkAvailable()) { ?>
								<li>
									<a href="javascript:void(0);" data-listing-available><?php echo JText::_('COM_ES_MARKETPLACES_MARK_AVAILABLE_LISTING');?></a>
								</li>
							<?php } ?>
						</ul>
					</div>
				</div>
				<?php } ?>
			</div>
		</div>

		<div class="es-apps-entry-section es-island">
			<div class="es-apps-entry-section__content">
				<?php if ($listing->isPendingProcess()) { ?>
					<div class="alert alert-info">
						<?php echo JText::_('COM_ES_MARKETPLACES_ITEM_PENDING_INFO');?>
					</div>
				<?php } ?>
				<?php if ($photos) { ?>
					<div class="swiper-container es-mkp-gallery-top" data-gallery-top>
						<div class="swiper-wrapper">
							<?php foreach ($photos as $photo) { ?>
								<div class="swiper-slide" style="background-image:url(<?php echo $photo['large']; ?>)"></div>
							<?php } ?>
						</div>
						<!-- Add Arrows -->
						<div class="swiper-button-next swiper-button-white"></div>
						<div class="swiper-button-prev swiper-button-white"></div>
					</div>
					<div class="swiper-container es-mkp-gallery-thumbs" data-gallery-thumbs>
						<div class="swiper-wrapper">
							<?php foreach ($photos as $photo) { ?>
								<div class="swiper-slide" style="background-image:url(<?php echo $photo['thumbnail']; ?>)"></div>
							<?php } ?>
						</div>
					</div>
				<?php } else { ?>
					<div class="embed-responsive embed-responsive-16by9">
						<img src="<?php echo $listing->getDefaultPhoto(); ?>" alt="" class="embed-responsive-item">
					</div>
				<?php } ?>

				<h1 class="es-apps-title">
					<?php echo $listing->getTitle();?>
				</h1>
				<div class="l-cluster l-spaces--xs t-lg-mb--lg">
					<div>
						<div class="">
							<div class="o-label2 o-label2--success"><?php echo $listing->getPriceTag(); ?></div>
						</div>

						<?php if ($listing->isSold()) { ?>
							<div class="">
								<div class="o-label2 o-label2--danger"><?php echo JText::_('COM_ES_MARKETPLACES_SOLD') ?></div>
							</div>
						<?php } else if ($listing->showStock()) { ?>
							<div class="">
								<?php if ($listing->isStockAvailable()) { ?>
									<div class="o-label2 o-label2--default"><?php echo JText::_('COM_ES_MARKETPLACES_IN_STOCK') ?></div>
								<?php } else { ?>
									<div class="o-label2 o-label2--danger"><?php echo JText::_('COM_ES_MARKETPLACES_OUT_OF_STOCK') ?></div>
								<?php } ?>
							</div>
						<?php } ?>

						<div class="">
							<?php if (!$listing->isOwner()) { ?>
								<button type="button" class="btn btn-es-primary"
									<?php if ($useConverseKit && $this->my->id) { ?>
									data-ck-chat="<?php echo $listing->user_id;?>"
									<?php } else { ?>
									data-es-conversations-compose
									data-id="<?php echo $listing->user_id;?>"
									<?php } ?>
									data-es-provide="tooltip"
									data-title="<?php echo $listing->getTitle(); ?>"
									data-message="<?php echo JText::sprintf('COM_ES_MARKETPLACES_CONVERSATION_MESSAGE', $listing->getTitle(), array('jsSafe' => true)); ?>"
									data-conversation-type="<?php echo SOCIAL_CONVERSATION_MARKETPLACE; ?>"
									>
									<i class="far fa-comment-dots"></i>&nbsp; <?php echo JText::_('COM_ES_MARKETPLACES_MESSAGE_SELLER'); ?>
								</button>
							<?php } ?>
						</div>
					</div>
				</div>

				<div class="l-cluster l-spaces--xs es-apps-entry__meta t-lg-mb--lg">
					<div>
						<span>
							<?php echo JText::sprintf('COM_ES_MARKETPLACES_BY', $this->html('html.' . $creator->getType(), $creator));?>
						</span>
						<span>&middot;</span>
						<span>
							<a href="<?php echo $listing->getCategory()->getFilterPermalink(); ?>">
								<?php echo JText::_($listing->getCategory()->title); ?>
							</a>
						</span>
						<span>&middot;</span>
						<span>
							<?php echo JText::sprintf('COM_EASYSOCIAL_VIDEOS_HITS', $listing->getHits()); ?>
						</span>

						<?php if ($listing->hasLocation()) { ?>
						<span>&middot;</span>
						<span class="es-listing-location">
							<a data-lng="<?php echo $listing->longitude;?>" data-lat="<?php echo $listing->latitude;?>"
								href="javascript:void(0)" data-map-location-link>
								<i class="fa fa-map-marker-alt"></i>&nbsp;
								<?php echo $listing->address;?>
							</a>
						</span>
						<?php } ?>
					</div>
				</div>

				<?php if ($listing->hasLocation()) { ?>
					<div class="es-stream-embed is-maps t-lg-mb--md t-hidden" data-map-preview>
						<div id="stream-map-preview" class="es-location-map <?php echo 'is-' . $this->config->get('location.provider'); ?> has-data" data-map-location data-latitude="<?php echo $listing->latitude; ?>" data-longitude="<?php echo $listing->longitude; ?>" data-location-provider="<?php echo $this->config->get('location.provider'); ?>"></div>
					</div>
				<?php } ?>

				<?php echo $this->render('module' , 'es-marketplaces-before-listing-description'); ?>

				<div class="es-market-content t-lg-mb--lg">
					<?php echo $listing->getDescription(); ?>
				</div>

				<?php echo $this->render('module' , 'es-marketplaces-after-listing-description'); ?>

				<?php if ($steps) { ?>
					<?php echo $this->output('site/marketplaces/item/fields', array('steps' => $steps, 'canEdit' => $this->my->isSiteAdmin(), 'objectId' => $listing->id, 'routerType' => 'marketplaces', 'item' => $listing)); ?>
				<?php } ?>

				<div class="es-actions es-bleed--bottom" data-stream-actions>
					<div class="es-actions__item es-actions__item-action">
						<div class="es-actions-wrapper">
							<ul class="es-actions-list">

								<?php if ($this->my->id) { ?>
									<?php if ($likes->hasReactions()) { ?>
										<li><?php echo $likes->button(); ?></li>
									<?php } ?>

									<li><?php echo $repost->button(); ?></li>
								<?php } ?>

								<?php if ($reports->canReport()) { ?>
									<li><?php echo $reports->html(); ?></li>
								<?php } ?>
							</ul>
						</div>
					</div>
					<div class="es-actions__item es-actions__item-stats">
						<?php echo $likes->html(); ?>
					</div>
					<div class="es-actions__item es-actions__item-comment">
						<?php echo $comments->getHTML();?>
					</div>
				</div>
			</div>
		</div>

		<?php echo $this->render('module' , 'es-marketplaces-before-other-listings'); ?>

		<div class="es-apps-entry-section">
			<div class="es-apps-entry-section__content">
				<?php if ($otherListings) { ?>
				<div class="es-listing-other">
					<?php echo $this->html('html.snackbar', 'COM_ES_MARKETPLACES_OTHER_LISTINGS'); ?>

					<div class="es-cards es-cards--3">
						<?php foreach ($otherListings as $otherListing) { ?>

							<?php echo $this->html('listing.marketplace', $otherListing, array(
									'style' => $this->isMobile() ? 'listing' : 'card',
									'browseView' => false,
									'from' => 'listing'
								)); ?>
						<?php } ?>
					</div>
				</div>
				<?php } ?>
			</div>
		</div>

		<?php echo $this->render('module' , 'es-marketplaces-after-other-listings'); ?>
	</div>
</div>

</div>
