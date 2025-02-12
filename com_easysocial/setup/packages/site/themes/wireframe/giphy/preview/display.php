<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div class="es-img-container has-bg t-lg-mb--md <?php echo !isset($giphy) || !$giphy ? 't-hidden' : ''; ?>" data-giphy-item-preview-placeholder>
	<a href="<?php echo isset($giphy) && $giphy ? $giphy : ''; ?>" target="_blank" class="es-img-container__wrap" data-giphy-permalink>
		<img style="max-width: 100%" src="<?php echo isset($giphy) && $giphy ? $giphy : ''; ?>" data-giphy-item-preview>
	</a>
</div>
