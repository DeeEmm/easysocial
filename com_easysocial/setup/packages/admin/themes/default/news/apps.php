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
	<div class="media mt-10">
		<div class="media-object pull-left">
			<img src="<?php echo $item->icon;?>" class="es-avatar es-avatar-apps" />
		</div>
		<div class="media-body">
			<div class="media-heading">
				<div class="app-title">
					<a href="<?php echo $item->link;?>" target="_blank"><?php echo $item->title;?></a>
				</div>
				<div class="app-listing-star">
					<?php if( $item->rating != 0 ){ ?>
						<?php for( $x = 0; $x < $item->rating; $x++ ){ ?>★<?php } ?>
					<?php } ?>

					<?php if( 5 - $item->rating != 0 ){ ?>
						<?php for( $i = 0; $i < 5 - $item->rating; $i++ ){ ?>☆<?php } ?>
					<?php } ?>
				</div>
			</div>
			<div class="app-meta mt-5">
				<?php echo JText::_( 'COM_EASYSOCIAL_IN' );?> <a href="<?php echo $item->categoryLink;?>" target="_blank"><?php echo $item->category;?></a> &middot;
				<?php echo JText::sprintf( 'COM_EASYSOCIAL_WIDGET_NEWS_LAST_UPDATED' , $item->lapsed ); ?>
			</div>
			<div class="app-info">
				<?php echo $this->html( 'string.escape' , $item->desc ); ?>
			</div>
		</div>
	</div>
</li>
<?php } ?>
