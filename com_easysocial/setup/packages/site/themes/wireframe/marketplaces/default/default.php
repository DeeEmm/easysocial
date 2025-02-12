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
	<?php if (!$browseView) { ?>
		<?php if ($cluster) { ?>
			<?php echo $this->html('cover.' . $cluster->getType(), $cluster, 'marketplaces'); ?>
		<?php } else if ($listingOwner){ ?>
			<?php echo $this->html('cover.user', $listingOwner, 'marketplaces'); ?>
		<?php } ?>
	<?php } ?>

	<div data-es-marketplaces class="es-container es-marketplaces" data-marketplaces-listing data-es-container
		data-filter="<?php echo $activeCategory ? 'category' : $filter; ?>"
		data-categoryid="<?php echo $activeCategory ? $activeCategory->id : 0; ?>"
		data-es-container
		data-clusterid="<?php echo $cluster ? $cluster->id : 0; ?>"
		data-from="<?php echo $from; ?>"
	>

		<?php echo $this->html('html.sidebar'); ?>

		<?php if ($this->isMobile()) { ?>
			<?php echo $this->includeTemplate('site/marketplaces/default/mobile.filters', $listings); ?>
		<?php } ?>

		<div class="es-content" data-wrapper>
			<?php echo $this->html('listing.loader', 'card', 4, 2, array('snackbar' => true, 'sortbar' => true)); ?>

			<?php if ($this->config->get('location.provider') == 'osm') { ?>
				<div id="map" class="t-hidden" data-osm-map></div>
			<?php } ?>
			<div class="es-detecting-location es-island" data-fetching-location>
				<i class="fa fa-globe-americas fa-spin"></i> <span data-detecting-location-message><?php echo JText::_('COM_ES_MARKETPLACES_DETERMINING_LOCATION'); ?></span>
			</div>

			<?php echo $this->render('module' , 'es-marketplaces-before-contents'); ?>
				<div class="marketplaces-content-wrapper" data-contents>
					<div>
						<?php echo $this->includeTemplate('site/marketplaces/default/wrapper'); ?>
					</div>
				</div>
			<?php echo $this->render('module' , 'es-marketplaces-after-contents'); ?>
		</div>
	</div>
</div>
