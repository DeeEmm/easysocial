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
$linkAttr = isset($attributes) ? implode(' ', $attributes) : '';
?>
<div class="es-list__item">
	<div class="es-list-item">
		<div class="es-list-item__context">
			<div class="es-list-item__hd">
				<div class="es-list-item__content">
					<div class="es-list-item__title">
						<a href="<?php echo $link; ?>" <?php echo $linkAttr; ?>><?php echo $linkTitle; ?></a>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
