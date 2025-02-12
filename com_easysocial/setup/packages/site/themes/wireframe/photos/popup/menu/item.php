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
<div data-photo-menu class="es-media-item-menu es-photo-menu-item">

	<div class="o-nav  o-nav--block es-photo-popup-nav">
		<?php if ($options['showTags'] && $this->config->get('photos.tagging')) { ?>
			<?php echo $this->includeTemplate('site/photos/popup/taglist'); ?>
		<?php } ?>

		<?php if ($lib->shareable()) { ?>
		<div class="o-nav__item" data-photo-share-button>
			<?php echo ES::sharing(array('url' => $photo->getPermalink(true, true), 'text' => JText::_('COM_EASYSOCIAL_PHOTOS_SHARE')))->getHTML(true); ?>
		</div>
		<?php } ?>

		<div class="o-nav__item" data-photo-report-button>
			<?php echo ES::reports()->form(SOCIAL_TYPE_PHOTO, $photo->id, array('dialogTitle' => 'COM_EASYSOCIAL_PHOTOS_REPORT_PHOTO_TITLE',
					'dialogContent' => 'COM_EASYSOCIAL_PHOTOS_REPORT_DESC',
					'title' => $photo->_('title'),
					'permalink' => $photo->getPermalink(true, true),
					'type' => 'link',
					'actorid' => $photo->user_id
				)
			);?>
		</div>

		<?php if ($lib->featureable()) { ?>
		<div class="o-nav__item <?php echo $photo->featured ? ' is-featured' : '';?>" data-photo-feature-button>
			<a href="javascript:void(0);" class="btn btn-photo-popup-nav-item">
				<i class="fa fa-star"></i>&nbsp; <?php echo JText::_('COM_EASYSOCIAL_FEATURE_THIS_PHOTO');?>
			</a>
		</div>
		<?php } ?>

		<?php if ($lib->hasDropdownMenu()) { ?>
		<div class="o-nav__item">
			<div class="dropdown_ ">
				<button data-es-toggle="dropdown" class="btn-photo-popup-action dropdown-toggle_" type="button">
					<i class="fa fa-ellipsis-h"></i>
				</button>

				<ul class="dropdown-menu dropdown-menu-right">
					<?php if ($lib->editable()) { ?>
					<li data-photo-edit-button>
						<a href="<?php echo $photo->getEditPermalink();?>"><?php echo JText::_("COM_EASYSOCIAL_PHOTOS_EDIT_PHOTO"); ?></a>
					</li>
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
						<a href="<?php echo FRoute::photos( array( 'id' => $photo->getAlias() , 'layout' => 'download' ) );?>">
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

					<?php if ($lib->canSetProfileCover()) { ?>
					<li data-photo-profileCover-button>
						<a href="<?php echo FRoute::profile( array( 'id' => $this->my->getAlias() , 'cover_id' => $photo->id ) );?>">
							<?php echo JText::_( 'COM_EASYSOCIAL_USE_AS_PROFILE_COVER' ); ?>
						</a>
					</li>
					<li class="divider"></li>
					<?php } ?>


					<?php if ($lib->albumLib->editable()) { ?>
					<li data-photo-cover-button>
						<a href="javascript: void(0);"><?php echo JText::_("COM_EASYSOCIAL_PHOTOS_SET_AS_ALBUM_COVER"); ?></a>
					</li>
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
		<?php } ?>
	</div>


</div>
<div class="es-photo-popup-close">
	<a href="javascript: void(0);" class="t-text--muted" data-popup-close-button>
		<i class="fa fa-times"></i>
	</a>
</div>
