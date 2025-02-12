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
<div class="btn-toolbar es-media-item-menu es-photo-menu-item" data-photo-menu>

	<div class="o-btn-group">
		<?php if ($lib->featureable()) { ?>
		<a href="javascript: void(0);" data-photo-feature-button class="btn btn-sm btn-es-default-o<?php echo $photo->featured ? ' is-featured' : '';?>">
			<i class="fa fa-star"></i>
		</a>
		<?php } ?>

		<?php if ($lib->hasDropdownMenu()) { ?>
		<div class="o-btn-group" data-item-actions-menu>
			<a href="javascript: void(0);" data-es-toggle="dropdown" class="btn btn-es-default-o btn-sm dropdown-toggle_">
				<i class="fa fa-cog"></i>
			</a>
			<ul class="dropdown-menu dropdown-menu-right">
				<?php if ($lib->editable()) { ?>
				<li data-photo-edit-button>
					<a href="<?php echo $photo->getEditPermalink();?>"><?php echo JText::_("COM_EASYSOCIAL_PHOTOS_EDIT_PHOTO"); ?></a>
				</li>
				<?php } ?>

				<?php if ($lib->shareable()) { ?>
				<li data-photo-share-button>
					<?php echo ES::sharing(array('url' => $photo->getPermalink(true, true), 'text' => 'COM_EASYSOCIAL_SHARE_PHOTO'))->html(false); ?>
				</li>
				<li class="divider"></li>
				<?php } ?>

				<?php if ($this->config->get('photos.original')) { ?>
				<li data-photo-original-button>
					<a href="<?php echo $photo->getSource('original');?>" target="_blank">
						<?php echo JText::_('COM_EASYSOCIAL_PHOTOS_VIEW_ORIGINAL');?>
					</a>
				</li>
				<?php } ?>

				<?php if ($lib->downloadable()) { ?>
				<li data-photo-download-button>
					<a href="<?php echo ESR::photos(array('id' => $photo->getAlias(), 'layout' => 'download'));?>">
						<?php echo JText::_("COM_EASYSOCIAL_DOWNLOAD_PHOTO"); ?>
					</a>
				</li>
				<?php } ?>

				<?php if ($lib->canSetProfilePicture()) { ?>
				<li data-photo-profileAvatar-button>
					<a href="javascript:void(0);">
						<?php echo JText::_("COM_EASYSOCIAL_USE_AS_PROFILE_AVATAR"); ?>
					</a>
				</li>
				<?php } ?>

				<?php if( $lib->canSetProfileCover()){ ?>
				<li data-photo-profileCover-button>
					<a href="<?php echo ESR::profile(array('id' => $this->my->getAlias(), 'cover_id' => $photo->id));?>">
						<?php echo JText::_('COM_EASYSOCIAL_USE_AS_PROFILE_COVER'); ?>
					</a>
				</li>
				<li class="divider"></li>
				<?php } ?>


				<?php if ($lib->albumLib->editable() && $lib->isAlbumOwner()) { ?>
				<li data-photo-cover-button>
					<a href="javascript:void(0);"><?php echo JText::_("COM_EASYSOCIAL_PHOTOS_SET_AS_ALBUM_COVER"); ?></a>
				</li>
				<?php } ?>

				<?php if ($lib->moveable()) { ?>
				<li data-photo-move-button>
					<a href="javascript:void(0);"><?php echo JText::_("COM_EASYSOCIAL_PHOTOS_MOVE_PHOTO_TO_ANOTHER_ALBUM"); ?></a>
				</li>
				<?php } ?>

				<?php if ($lib->deleteable()) { ?>
				<li data-photo-delete-button>
					<a href="javascript:void(0);"><?php echo JText::_("COM_EASYSOCIAL_PHOTOS_DELETE_PHOTO"); ?></a>
				</li>
				<?php } ?>
			</ul>
		</div>
		<?php } ?>
	</div>

	<div class="o-btn-group">
		<?php if ($lib->shareable()) {  ?>
		<div class="btn btn-sm btn-es-default-o" data-photo-share-button>
			<?php echo ES::sharing(array('url' => $photo->getPermalink(true, true), 'text' => JText::_( 'COM_EASYSOCIAL_PHOTOS_SHARE')))->html(true, false); ?>
		</div>
		<?php } ?>

		<?php if (ES::reports()->canReport()) { ?>
		<div class="btn btn-sm btn-es-default-o" data-photo-report-button>
			<?php echo ES::reports()->getForm('com_easysocial', SOCIAL_TYPE_PHOTO, $photo->id, $photo->get('title'), JText::_('COM_EASYSOCIAL_PHOTOS_REPORT'), JText::_('COM_EASYSOCIAL_PHOTOS_REPORT_PHOTO_TITLE'), JText::_('COM_EASYSOCIAL_PHOTOS_REPORT_DESC'), $photo->getPermalink(true, true), true, $photo->user_id); ?>
		</div>
		<?php } ?>
	</div>
</div>
