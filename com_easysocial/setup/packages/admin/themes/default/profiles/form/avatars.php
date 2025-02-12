<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div class="profileAvatarForm" data-profile-avatars data-id="<?php echo $profile->id;?>">
	<div class="row">
		<div class="col-md-8">
			<div class="panel">
				<?php echo $this->html('panel.heading', 'COM_ES_PROFILES_FORM_DEFAULT_PROFILE_PICTURES'); ?>

				<div class="panel-body">
					<div class="row">
						<ul class="g-list-unstyled avatarList images-list images-list-avatars" data-profile-avatars-list>
						<?php if ($defaultAvatars) { ?>
							<?php echo $this->loadTemplate('admin/profiles/avatar.item' , array( 'defaultAvatars' => $defaultAvatars ) ); ?>
						<?php } ?>
						</ul>

						<?php if (!$defaultAvatars) { ?>
						<div class="is-empty">
							<div class="empty center" data-profile-avatars-empty>
								<?php echo JText::_( 'COM_EASYSOCIAL_PROFILE_NO_DEFAULT_AVATARS_YET' );?>
							</div>
						</div>
						<?php } ?>
					</div>
				</div>
			</div>
		</div>

		<div class="col-md-4 uploadAvatarNav">
			<div class="panel">
				<div class="panel-head">
					<b><?php echo JText::_('COM_EASYSOCIAL_PROFILES_FORM_AVATAR_HEADING_UPLOAD_NEW_AVATARS');?></b>
				</div>
				<div class="panel-body">
					<div id="avatarUploadContainer" class="accordion-body in">
						<div class="wbody wbody-padding">
							<div class="form-uploader filesForm" data-profile-avatars-uploader>

								<!-- Uploader queue -->
								<div class="upload-queue">

									<!-- Clear items -->
									<div class="clearfix">
										<a href="javascript:void(0)" class="btn btn-es-default-o btn-sm clear-uploaded-items pull-right" data-uploader-clear>
											<i class="fa fa-trash"></i> <?php echo JText::_('COM_EASYSOCIAL_CLEAR_HISTORY_BUTTON'); ?>
										</a>
									</div>

									<!-- Placeholder for upload items -->
									<div class="upload-queue" data-uploaderQueue></div>
								</div>

								<div class="upload-submit uploaderForm" data-uploader-form>

									<button class="btn btn-es-default-o btn-sm uploadButton" href="javascript:void(0);" data-uploader-browse>
										<i class="fa fa-upload"></i> <?php echo JText::_('COM_EASYSOCIAL_ADD_FILES_BUTTON'); ?>
									</button>

									<a href="javascript:void(0);" class="btn btn-es-primary-o btn-sm" data-profile-avatars-startupload><?php echo JText::_( 'COM_EASYSOCIAL_START_UPLOAD_BUTTON' ); ?></a>

									<span class="help-block drop-files-wrap" id="uploaderDragDrop">
										<?php echo JText::_('COM_EASYSOCIAL_UPLOADER_DROP_YOUR_FILES'); ?>
									</span>
								</div>

								<?php echo $this->html('uploader.queue'); ?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
