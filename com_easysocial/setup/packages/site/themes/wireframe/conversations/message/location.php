<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2014 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined( '_JEXEC' ) or die( 'Unauthorized Access' );
?>
<?php if ($location && $this->config->get('conversations.location')) { ?>
<?php $isPopup = $this->config->get('location.provider') !== 'osm'; ?>
<div class="es-locations" data-location-base>
	<div class="t-fs--sm t-lg-mt--lg t-lg-mb--lg" data-location-text>
		<i class="fa fa-map-marker-alt "></i> <?php echo JText::_( 'COM_EASYSOCIAL_CONVERSATIONS_MESSAGE_POSTED_FROM' );?>
		<u>
			<a href="<?php echo $isPopup ? $location->getMapUrl() : 'javascript:void(0)'; ?>" data-location-link
				<?php if ($isPopup) { ?>
					data-popbox="module://easysocial/locations/popbox"
					data-lat="<?php echo $location->latitude; ?>"
					data-lng="<?php echo $location->longitude; ?>"
					data-location-provider="<?php echo $this->config->get('location.provider'); ?>"
				<?php } ?>
			>
				<?php echo $location->getAddress(30); ?>
			</a>
		</u>
	</div>

	<?php if (!$isPopup) { ?>
		<div class="es-location-map t-lg-mb--lg t-hidden <?php echo 'is-' . $this->config->get('location.provider'); ?> has-data" data-location-map data-latitude="<?php echo $location->latitude; ?>" data-longitude="<?php echo $location->longitude; ?>" data-location-provider="osm"></div>
	<?php } ?>
</div>
<?php } ?>

