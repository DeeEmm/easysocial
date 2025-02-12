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
<div class="es-list__item">
	<div class="es-list-item es-island <?php echo $listing->isFeatured() ? ' is-featured' : '';?>" data-item data-id="<?php echo $listing->id;?>">
		<div class="es-list-item__media">
			<?php echo $this->html('avatar.mini', $listing->getTitle(), $listing->getPermalink(), $listing->getSinglePhoto()); ?>
		</div>

		<div class="es-list-item__context">
			<div class="es-list-item__hd">
				<div class="es-list-item__content">
					<div class="es-list-item__title">
						<a href="<?php echo $listing->getPermalink(true, null, null, $from);?>"><?php echo $listing->getTitle();?></a>
					</div>
					
					<div class="es-list-item__meta">
						<ol class="g-list-inline g-list-inline--delimited t-text--muted">
							<li>
								<a href="<?php echo $listing->getCategory()->getFilterPermalink();?>"><?php echo $listing->getPriceTag(); ?></a>
							</li>
							<li data-breadcrumb="&#183;">
								<a href="<?php echo $listing->getCategory()->getPermalink();?>"><?php echo $listing->getCategory()->getTitle();?></a>
							</li>
							<li data-breadcrumb="&#183;">
								<?php echo JText::sprintf('COM_ES_MARKETPLACES_BY', $this->html('html.user', $listing->getCreator(), true));?>
							</li>
							<li data-breadcrumb="&#183;">
								<i class="fa fa-heart"></i> <?php echo $listing->getLikesCount();?>
							</li>
							<li data-breadcrumb="&#183;">
								<i class="fa fa-comment"></i> <?php echo $listing->getCommentsCount();?>
							</li>
						</ol>
					</div>
				</div>
				<div class="es-list-item__state">
					<div class="es-label-state es-label-state--featured" data-original-title="<?php echo JText::_('COM_EASYSOCIAL_FEATURED');?>" data-es-provide="tooltip">
						<i class="es-label-state__icon"></i>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>