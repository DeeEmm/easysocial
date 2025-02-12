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
<?php foreach( $defaultAvatars as $avatar ){ ?>
	<li class="avatarItem hover-panel<?php echo $avatar->getState() == SOCIAL_STATE_PUBLISHED ? ' published' : ' unpublished';?><?php echo $avatar->default ? ' default' : '';?>" data-id="<?php echo $avatar->id;?>"
		data-profile-avatars-item
	>
		<div class="thumbnail clearfix">
			<i class="fa fa-star images-list-avatars__state"></i>

			<img src="<?php echo $avatar->getSource( SOCIAL_AVATAR_SQUARE );?>" title="<?php echo $this->html( 'string.escape' , $avatar->title );?>" class="avatarImage" />

			<div class="avatarActions hover-panel-show">

				<a href="javascript:void(0);" class="btn btn-default btn-xs"
					data-es-provide="tooltip"
					data-placement="top"
					data-original-title="<?php echo JText::_( 'COM_EASYSOCIAL_PROFILES_FORM_AVATAR_SET_DEFAULT_AVATAR' , true );?>"
					data-avatar-default
				><i class="fa fa-star "></i> </a>

				<a href="javascript:void(0);" class="btn btn-default btn-xs btn-delete"
					data-es-provide="tooltip"
					data-placement="top"
					data-avatar-delete
					data-original-title="<?php echo JText::_( 'COM_EASYSOCIAL_PROFILES_FORM_AVATAR_DELETE_AVATAR' , true );?>" >
					<i class="fa fa-times "></i>
				</a>

				<?php if( $avatar->getState() == SOCIAL_STATE_DEFAULT ) { ?>
					<b><?php echo JText::_( 'COM_EASYSOCIAL_PROFILES_FORM_AVATAR_DEFAULT_AVATAR' ); ?></b>
				<?php } ?>

			</div>
		</div>
	</li>
<?php } ?>
