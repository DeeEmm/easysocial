<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div data-field-avatar class="es-field-avatar"
	data-error-empty="<?php echo JText::_('PLG_FIELDS_AVATAR_VALIDATION_EMPTY_PROFILE_PICTURE', true);?>"
>
	<ul class="g-list-unstyled">
		<li class="<?php echo !empty( $imageSource ) ? ' selected' : '';?>">
			<div class="avatar-wrap-frame">
				<div data-field-avatar-frame style="background-image: url(<?php echo $imageSource ? $imageSource : $systemAvatar; ?>)" class="avatar-frame">
					<div data-field-avatar-viewport class="avatar-viewport"></div>
				</div>
				<div class="avatar-remove" data-field-avatar-remove <?php if( !$hasAvatar ) { ?>style="display: none;"<?php } ?>>
					<a href="javascript:void(0);" data-field-avatar-remove-button>×</a>
				</div>
			</div>

			<div data-field-avatar-note style="display: none;"><?php echo JText::_( 'PLG_FIELDS_AVATAR_CROP_PHOTO' ); ?></div>

			<div data-field-avatar-actions style="display: none;">
				<button type="button" class="btn btn-es-default" data-field-avatar-actions-cancel><?php echo JText::_('COM_ES_CANCEL'); ?></button>
			</div>
		</li>

		<li data-field-avatar-revert style="display: none;">
			<a href="javascript:void(0);" data-field-avatar-revert-button><?php echo JText::_('PLG_FIELDS_AVATAR_REVERT_BUTTON'); ?></a>
		</li>

		<?php if ($params->get('upload')) { ?>
		<li>
			<div class="avatar-upload-field">
				<div class="o-input-group">
					<label for="upload-avatar" class="t-hidden">Upload Avatar</label>
					<input id="upload-avatar" class="o-form-control" type="text" readonly />
					<span class="o-input-group__btn">
						<span class="btn btn-es-default btn-file" data-browse-avatar>
							<?php echo JText::_('FIELDS_USER_AVATAR_BROWSE_FILE'); ?>&hellip; <input type="file" id="<?php echo $inputName; ?>" name="<?php echo $inputName; ?>[file]" data-field-avatar-file />
						</span>
					</span>
				</div>
			</div>
		</li>
		<?php } ?>

		<?php if( $avatars && $params->get( 'gallery', true ) ){ ?>
		<li class="t-lg-mt--lg" <?php if( !$params->get( 'use_gallery_button' ) ) { ?>style="display: none;"<?php } ?>>
			<a class="btn btn-es-default btn-sm" href="javascript:void(0);" data-field-avatar-gallery>
				<i class="fa fa-photo t-lg-mr--md"></i> <?php echo JText::_( 'PLG_FIELDS_AVATAR_SELECT_AVATAR_BUTTON' ); ?>
			</a>
		</li>

		<?php if( !$params->get( 'use_gallery_button' ) ) { ?>
		<li>
			<h3><?php echo JText::_( 'PLG_FIELDS_AVATAR_GALLERY_SELECTION' ); ?></h3>
		</li>
		<?php } ?>

		<?php if( !empty( $avatars ) ) { ?>
		<li>
			<div class="o-avatar-list t-lg-mt--lg" <?php if( $params->get( 'use_gallery_button' ) ) { ?>style="display: none;"<?php } ?> data-field-avatar-gallery-items>
				<?php foreach( $avatars as $avatar ){ ?>
				<div class="o-avatar-list__item avatarItem" data-field-avatar-gallery-item data-id="<?php echo $avatar->id;?>">
					<?php echo $this->html('avatar.mini', '', 'javascript:void(0);', $avatar->getSource(SOCIAL_AVATAR_MEDIUM)); ?>
				</div>
				<?php } ?>
			</div>
		</li>
		<?php } ?>
		<?php } ?>
	</ul>

	<div class="t-text--danger" data-field-error></div>

	<input type="hidden" name="<?php echo $inputName; ?>[source]" data-field-avatar-source value="<?php echo $defaultAvatarId;?>" />
	<input type="hidden" name="<?php echo $inputName; ?>[path]" data-field-avatar-path />
	<input type="hidden" name="<?php echo $inputName; ?>[data]" data-field-avatar-data />
	<input type="hidden" name="<?php echo $inputName; ?>[type]" data-field-avatar-type value="<?php echo !$hasAvatar && $defaultAvatarId ? 'gallery' : '';?>" />
	<input type="hidden" name="<?php echo $inputName; ?>[name]" data-field-avatar-name />
</div>
