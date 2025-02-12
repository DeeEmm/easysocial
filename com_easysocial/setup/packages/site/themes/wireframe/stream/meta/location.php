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
<?php if (!empty($stream->location)) { ?>
	<?php $isPopup = $this->config->get('stream.location.style') === 'popup' && $this->config->get('location.provider') !== 'osm'; ?>
	<?php echo JText::_('COM_EASYSOCIAL_STREAM_AT' ); ?>

	<a href="<?php echo $isPopup ? $stream->location->getMapUrl() : 'javascript:void(0)'; ?>" data-location-link
		<?php if ($isPopup) { ?>
			target="_blank"
			data-popbox="module://easysocial/locations/popbox"
			data-popbox-position="top-left"
			data-popbox-collision="flip none"
			data-lat="<?php echo $stream->location->latitude; ?>"
			data-lng="<?php echo $stream->location->longitude; ?>"
			data-location-provider="<?php echo $this->config->get('location.provider'); ?>"
		<?php } ?>
		>
		<?php echo $stream->location->address; ?>
	</a>
<?php } ?>
