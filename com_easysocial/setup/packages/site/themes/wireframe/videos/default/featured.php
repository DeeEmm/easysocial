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
defined( '_JEXEC' ) or die( 'Unauthorized Access' );
?>
<div class="es-videos-featured" data-videos-featured>
    <?php echo $this->output('site/videos/default/items', array('featuredVideos' => '', 'videos' => $featuredVideos, 'rawUid' => $rawUid, 'type' => $type, 'isFeatured' => true, 'returnUrl' => $returnUrl, 'browseView' => $browseView, 'sort' => $sort, 'sortItems' => $sortItems, 'filterOutput' => '')); ?>
</div>
