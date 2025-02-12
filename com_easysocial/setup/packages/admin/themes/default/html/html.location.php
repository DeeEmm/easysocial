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
<a data-lng="<?php echo $longitude;?>" data-lat="<?php echo $latitude;?>"
	href="javascript:void(0)" data-map-location-link>
	<?php if ($displayIcon) { ?><i class="fa fa-map-marker-alt"></i>&nbsp;<?php } ?>
	<?php echo $address;?>
</a>
