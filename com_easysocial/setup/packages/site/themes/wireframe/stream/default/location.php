<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div id="stream-map-preview" class="es-location-map <?php echo $isEdit ? 'is-edited' : ''; ?> <?php echo 'is-' . $this->config->get('location.provider'); ?> has-data" data-location-map data-latitude="<?php echo $stream->location->latitude; ?>" data-longitude="<?php echo $stream->location->longitude; ?>" data-location-provider="<?php echo $this->config->get('location.provider'); ?>"></div>
