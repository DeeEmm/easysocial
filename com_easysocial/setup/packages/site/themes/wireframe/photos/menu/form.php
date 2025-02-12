<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2016 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div class="btn-toolbar es-media-item-menu es-photo-menu-form" data-photo-menu>

	<div class="o-btn-group">
		<a class="btn btn-es-default-o btn-sm" data-photo-cancel-button href="<?php echo $photo->getPermalink(); ?>"><?php echo JText::_("COM_ES_CANCEL"); ?></a>

		<a class="btn btn-es-default-o btn-sm" data-photo-done-button href="<?php echo $photo->getPermalink();?>" title="<?php echo $this->html('string.escape', $photo->get('title'));?>">
			<i class="fa fa-check"></i> <?php echo JText::_('COM_EASYSOCIAL_DONE_BUTTON');?>
		</a>
	</div>

	<?php if ( $lib->canRotatePhoto() ){ ?>
	<div class="o-btn-group">
			<a data-photo-rotateLeft-button href="javascript: void(0);" class="btn btn-es-default-o btn-sm">
				<i class="fa fa-undo"></i>
			</a>

			<a data-photo-rotateRight-button href="javascript: void(0);" class="btn btn-es-default-o btn-sm">
				<i class="fa fa-undo fa-flip-horizontal"></i>
			</a>
	</div>
	<?php } ?>

	<div class="o-btn-group" data-item-actions-menu>
		<a href="javascript:void(0);" class="btn btn-es-default-o btn-sm dropdown-toggle_" data-es-toggle="dropdown">
			<i class="fa fa-angle-down"></i> <span><?php echo JText::_('COM_EASYSOCIAL_PHOTOS_EDIT'); ?></span>
		</a>
		<ul class="dropdown-menu dropdown-menu-right">

			<?php if ($lib->editable()) { ?>
			<li data-photo-cover-button>
				<a href="javascript: void(0);"><?php echo JText::_("COM_EASYSOCIAL_PHOTOS_SET_AS_ALBUM_COVER"); ?></a>
			</li>
			<li class="divider"></li>
			<?php } ?>

			<?php if( $lib->downloadable() ){ ?>
			<li data-photo-download-button>
				<a href="<?php echo FRoute::photos( array( 'layout' => 'download' , 'id' => $photo->getAlias() ) );?>">
					<?php echo JText::_("COM_EASYSOCIAL_DOWNLOAD_PHOTO"); ?>
				</a>
			</li>
			<li class="divider"></li>
			<?php } ?>



			<?php if( $lib->moveable() ){ ?>
			<li data-photo-move-button>
				<a href="javascript: void(0);"><?php echo JText::_("COM_EASYSOCIAL_PHOTOS_MOVE_PHOTO_TO_ANOTHER_ALBUM"); ?></a>
			</li>
			<?php } ?>

			<?php if( $lib->deleteable() ){ ?>
			<li data-photo-delete-button>
				<a href="javascript: void(0);"><?php echo JText::_("COM_EASYSOCIAL_PHOTOS_DELETE_PHOTO"); ?></a>
			</li>
			<?php } ?>
		</ul>
	</div>


</div>
