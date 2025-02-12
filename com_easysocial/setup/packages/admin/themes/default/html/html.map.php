<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2015 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div class="es-stream-embed is-maps t-lg-mb--md t-hidden" data-map-preview>
	<div id="stream-map-preview" class="es-location-map <?php echo 'is-' . $this->config->get('location.provider'); ?> has-data" data-map-location data-latitude="<?php echo $latitude; ?>" data-longitude="<?php echo $longitude; ?>" data-location-provider="<?php echo $this->config->get('location.provider'); ?>"></div>
</div>
