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
<div class="es-giphy-wrapper <?php echo isset($giphy) && $giphy ? '' : 't-hidden'; ?>" data-giphy-placeholder>
	<div class="es-giphy-wrapper__remove" data-giphy-remove></div>

	<div class="es-giphy-wrapper__item">

		<div class="es-img-container has-bg" data-giphy-container>
			<span class="es-img-container__wrap">
				<img src="<?php echo isset($giphy) && $giphy ? $giphy : ''; ?>" data-giphy-preview>
			</span>
		</div>
	</div>
</div>
