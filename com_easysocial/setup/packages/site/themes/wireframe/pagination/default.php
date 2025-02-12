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
<div class="o-pagination-wrap">
<ul class="o-pagination">
	<?php if( $data->previous ){ ?>
	<li>
		<a href="<?php echo !$data->previous->link ? 'javascript:void(0);' : $data->previous->link;?>"
			class="previousItem<?php echo !$data->previous->link ? ' disabled' : '';?>"
			data-es-provide="tooltip"
			data-original-title="<?php echo JText::_( 'COM_EASYSOCIAL_PAGINATION_PREVIOUS_PAGE' );?>"
			data-placement="bottom"
			rel="prev">&laquo;</a>
	</li>
	<?php } ?>

	<?php foreach( $data->pages as $page ){ ?>
		<?php   if( $page->link ) { ?>
			<li class="<?php echo !$page->link ? ' active disabled' : '';?>">
				<a href="<?php echo !$page->link ? 'javascript:void(0);' : $page->link;?>"
					data-limitstart="<?php echo $page->base ? $page->base : 0;?>"
					data-es-provide="tooltip"
					data-original-title="<?php echo JText::sprintf( 'COM_EASYSOCIAL_PAGINATION_PAGE' , $page->text );?>"
					data-placement="bottom"
				><?php echo $page->text;?></a>
			</li>
		<?php } else { ?>
			<li class="active"><span><?php echo $page->text;?></span></li>
		<?php } ?>
	<?php } ?>

	<?php if( $data->next ){ ?>
	<li>
		<a href="<?php echo !$data->next->link ? 'javascript:void(0);' : $data->next->link;?>"
			class="nextItem<?php echo !$data->next->link ? ' disabled' :'';?>"
			rel="next"
			data-es-provide="tooltip"
			data-placement="bottom"
			data-original-title="<?php echo JText::_( 'COM_EASYSOCIAL_PAGINATION_NEXT_PAGE' );?>">
		&raquo;
		</a>
	</li>
	<?php } ?>
</ul>
</div>
