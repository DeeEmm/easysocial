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
<div data-photo-menu class="es-media-item-menu es-photo-menu-form">

	<div class="o-btn-group">
		<a href="javascript:void(0);" class="btn btn-es-default-o btn-sm" data-photo-cancel-button>
			<i class="fa fa-times"></i> <?php echo JText::_('COM_ES_CANCEL');?>
		</a>

		<a href="<?php echo $photo->getPermalink();?>" title="<?php echo $this->html('string.escape', $photo->_('title'));?>" class="btn btn-es-default-o btn-sm" data-photo-done-button>
			<i class="fa fa-check"></i> <?php echo JText::_('COM_EASYSOCIAL_DONE_BUTTON');?>
		</a>
	</div>

	<?php if ($lib->canRotatePhoto()) { ?>
	<div class="o-btn-group">
		<a href="javascript: void(0);" class="btn btn-es-default-o btn-sm" data-photo-rotateLeft-button>
			<i class="fa fa-undo"></i>
		</a>

		<a href="javascript: void(0);" class="btn btn-es-default-o btn-sm" data-photo-rotateRight-button>
			<i class="fa fa-undo fa-flip-horizontal"></i>
		</a>
	</div>
	<?php } ?>

	<div class="o-btn-group dropdown_" data-item-actions-menu>
		<a href="javascript: void(0);" class="btn btn-es-default-o btn-sm " data-es-toggle="dropdown">
			<span><?php echo JText::_('COM_EASYSOCIAL_PHOTOS_EDIT'); ?></span> &nbsp;<i class="fa fa-angle-down"></i>
		</a>
		<ul class="dropdown-menu">
			<?php if ($lib->downloadable()) { ?>
			<li data-photo-download-button>
				<a href="<?php echo ESR::photos(array('layout' => 'download' , 'id' => $photo->getAlias() ) );?>">
					<?php echo JText::_("COM_EASYSOCIAL_DOWNLOAD_PHOTO"); ?>
				</a>
			</li>
			<li class="divider"></li>
			<?php } ?>

			<?php if ($lib->moveable()) { ?>
			<li data-photo-move-button>
				<a href="javascript: void(0);"><?php echo JText::_("COM_EASYSOCIAL_PHOTOS_MOVE_PHOTO_TO_ANOTHER_ALBUM"); ?></a>
			</li>
			<?php } ?>

			<?php if ($lib->deleteable()) { ?>
			<li data-photo-delete-button>
				<a href="javascript: void(0);"><?php echo JText::_("COM_EASYSOCIAL_PHOTOS_DELETE_PHOTO"); ?></a>
			</li>
			<?php } ?>
		</ul>
	</div>
</div>
