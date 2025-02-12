<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2014 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined( '_JEXEC' ) or die( 'Unauthorized Access' );
?>
<?php foreach( $items as $item ){ ?>
<li class="newsItem" widget-news-item>
	<div class="es-media">
		<div class="es-media-object">
			<div class="es-date"><span><?php echo $item->day;?></span><?php echo $item->month;?></div>
		</div>
		<div class="es-media-body">
			<span class="updates-news">
				<h5><?php echo $item->title;?></h5>
				<p><?php echo $item->content;?></p>
			</span>
			<span class="updates-news" style="margin-top:5px;">
				<?php echo JText::_( 'COM_EASYSOCIAL_WIDGET_NEWS_BY' );?> <?php echo $item->author;?>
			</span>
		</div>
	</div>
</li>
<?php } ?>
