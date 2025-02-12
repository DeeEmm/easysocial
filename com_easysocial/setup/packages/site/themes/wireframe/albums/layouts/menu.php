<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div class="es-album-menu es-media-item-menu es-album-menu-item">

	<?php if ($options['canUpload'] && $lib->canUpload()) { ?>
	<div class="o-btn-group o-btn-group--album-upload" data-album-upload-button>
		<a href="javascript:void(0);" class="btn btn-es-default-o btn-sm">
			<i class="fa fa-upload"></i>&nbsp; <?php echo JText::_("COM_EASYSOCIAL_ALBUMS_ADD_PHOTOS"); ?>
		</a>
	</div>
	<?php } ?>

	<?php if ($lib->canUpload() || $lib->editable() || $lib->deleteable() || $creator->isFriends($this->my->id)) { ?>
	<div class="o-btn-group">

		<?php if ($lib->canFavourite()) { ?>
			<a href="javascript:void(0);" class="btn btn-es-default-o btn-sm btn-album-favourite <?php echo $album->isFavourite($this->my->id)? ' active is-fav' : '' ?>" data-album-favourite-button>
				<i class="fa fa-star"></i>&nbsp; <span><?php echo JText::_('COM_EASYSOCIAL_ALBUMS_FAVOURITE_ALBUM'); ?></span>
			</a>
		<?php } ?>

		<?php if ($lib->editable() || $lib->deleteable()) { ?>
		<a href="javascript:void(0);" class="btn btn-es-default-o btn-sm dropdown-toggle_" data-es-toggle="dropdown">
			<i class="fa fa-cog"></i>
		</a>

		<ul class="dropdown-menu">
			<?php if ($lib->editable()) { ?>
			<li data-album-edit-button>
				<a href="<?php echo $album->getEditPermalink();?>"><?php echo JText::_('COM_EASYSOCIAL_ALBUMS_EDIT_ALBUM'); ?></a>
			</li>
			<?php } ?>

			<?php if ($lib->deleteable()) { ?>
			<li class="divider"></li>
			<li data-album-delete-button>
				<a href="javascript:void(0);"><?php echo JText::_("COM_EASYSOCIAL_ALBUMS_DELETE_ALBUM"); ?></a>
			</li>
			<?php } ?>
		</ul>
		<?php } ?>
	</div>
	<?php } ?>

	<?php if ($this->config->get('sharing.enabled') || ($this->config->get('reports.enabled') && $this->access->allowed('reports.submit'))) { ?>
	<div class="o-btn-group">
		<?php if ($this->config->get('sharing.enabled')) { ?>
			<?php echo $this->html('album.bookmark', $album); ?>
		<?php } ?>

		<?php echo $this->html('album.report', $album); ?>
	</div>
	<?php } ?>
</div>

<?php // When editing / creating an album ?>
<?php if ($lib->editable()) { ?>
<div class="es-album-menu es-media-item-menu es-album-menu-form">
	<?php if ($options['canUpload'] && $lib->canUpload()) { ?>
	<div class="o-btn-group o-btn-group--album-upload" data-album-upload-button>
		<a href="javascript:void(0);" class="btn btn-es-default-o btn-sm">
			<i class="fa fa-upload"></i>&nbsp; <?php echo JText::_("COM_EASYSOCIAL_ALBUMS_ADD_PHOTOS"); ?>
		</a>
	</div>
	<?php } ?>

	<div class="o-btn-group">
		<?php if ($album->id) { ?>
		<a href="<?php echo $album->getPermalink();?>" class="btn btn-es-default-o btn-sm" data-album-cancel-button>
			<i class="fa fa-times"></i>&nbsp; <?php echo JText::_("COM_ES_CANCEL"); ?>
		</a>
		<?php } ?>

		<a href="<?php echo $album->getPermalink(); ?>" class="btn btn-es-primary-o btn-sm" data-album-done-button>
			<i class="fa fa-check"></i>&nbsp; <?php echo JText::_("COM_EASYSOCIAL_ALBUMS_DONE"); ?>
		</a>
	</div>

</div>
<?php } ?>
