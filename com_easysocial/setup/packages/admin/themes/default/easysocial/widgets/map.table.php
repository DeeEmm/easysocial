<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<table class="table table-striped mt-20">
	<tr>
		<td width="40%">
			<?php echo JText::_('COM_EASYSOCIAL_WIDGET_MEMBER_LOCATION_COUNTRY'); ?>
		</td>
		<td class="center">
			<?php echo JText::_( 'COM_EASYSOCIAL_WIDGET_MEMBER_LOCATION_TOTAL_USERS' );?>
		</td>
	</tr>
	<?php foreach( $countries as $country ){ ?>
	<tr data-country="<?php echo $country->country;?>">
		<td data-stat-country="<?php echo $country->country;?>">
			<?php echo $country->country;?>
		</td>
		<td class="center" data-counter>
			<?php echo $country->total;?>
		</td>
	</tr>
	<?php } ?>
</table>
