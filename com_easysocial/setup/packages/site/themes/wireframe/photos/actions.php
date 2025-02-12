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
defined( '_JEXEC' ) or die( 'Unauthorized Access' );
?>
<?php if( $photo->taggable() ){ ?>
<li data-photo-tag-button>
	<a href="javascript: void(0);"><?php echo JText::_("COM_EASYSOCIAL_TAG_PHOTO"); ?></a>
</li>
<?php } ?>

<?php if( $photo->shareable() ){ ?>
<li data-photo-share-button>
	<?php echo ES::get( 'Sharing' , array( 'url' => $photo->getPermalink() , 'text' => JText::_( 'COM_EASYSOCIAL_SHARE_PHOTO' ) , 'display' => 'dialog' ) )->html(true, false); ?>
</li>
<li class="divider"></li>
<?php } ?>

<?php if( $photo->canSetProfilePicture() ){ ?>
<li data-photo-profileAvatar-button>
	<a href="javascript:void(0);">
		<?php echo JText::_("COM_EASYSOCIAL_USE_AS_PROFILE_AVATAR"); ?>
	</a>
</li>
<?php } ?>

<?php if( $photo->canSetProfileCover() ){ ?>
<li data-photo-profileCover-button>
	<a href="<?php echo FRoute::profile( array( 'id' => $this->my->getAlias() , 'cover_id' => $photo->id ) );?>">
		<?php echo JText::_("COM_EASYSOCIAL_USE_AS_PROFILE_COVER"); ?>
	</a>
</li>
<?php } ?>

<?php if( $photo->downloadable() ){ ?>
<li data-photo-download-button>
	<a href="<?php echo FRoute::photos( array( 'layout' => 'download' , 'id' => $photo->getAlias() ) );?>">
		<?php echo JText::_("COM_EASYSOCIAL_DOWNLOAD_PHOTO"); ?>
	</a>
</li>
<li class="divider"></li>
<?php } ?>

<li data-photo-report-button>
	<?php echo ES::reports()->getForm('com_easysocial', SOCIAL_TYPE_PHOTO, $photo->id, $photo->get('title'), JText::_('COM_EASYSOCIAL_REPORT_PHOTO'), '', JText::_('COM_EASYSOCIAL_REPORT_PHOTO_DESC'), $photo->getPermalink(true, true), false, $photo->user_id); ?>
</li>

<?php if( $photo->deleteable() ){ ?>
<li data-photo-delete-button>
	<a href="javascript: void(0);"><?php echo JText::_("COM_EASYSOCIAL_DELETE_PHOTO"); ?></a>
</li>
<?php } ?>
