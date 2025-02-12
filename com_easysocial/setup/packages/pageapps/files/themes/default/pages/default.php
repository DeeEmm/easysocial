<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2016 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div class="app-explorer app-pages" data-id="<?php echo $page->id;?>">
	<div class="app-contents-wrap">
		<?php echo $explorer->render('site/controllers/pages/explorer', array('allowUpload' => $allowUpload, 'uploadLimit' => $uploadLimit, 'showUse' => false, 'showClose' => false, 'controllerName' => 'pages', 'allowedExtensions' => $allowedExtensions)); ?>
	</div>
</div>

