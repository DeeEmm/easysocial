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
<div class="es-container">
	<div class="es-content">
		<?php echo $this->html('html.snackbar', 'COM_EASYSOCIAL_REGISTRATIONS_SELECT_PROFILE_TYPE_TITLE'); ?>

		<?php if ($profiles) { ?>
			<?php if (!$useDropdownList) { ?>
			<div class="list-profiles-type">
				<?php foreach ($profiles as $profile) { ?>
					<?php echo $this->loadTemplate('site/registration/default/items', array('profile' => $profile)); ?>
				<?php } ?>
			</div>
			<?php } else { ?>
			<div class="o-control-input">
				<select class="o-form-control" data-profile-select>
				<option value="" selected="selected"><?php echo JText::_('COM_ES_SELECT_PROFILE_TYPE'); ?></option>
				<?php foreach ($profiles as $profile) { ?>
					<option value="<?php echo ESR::registration(array('controller' => 'registration', 'task' => 'selectType' , 'profile_id' => $profile->id));?>"
							data-profile-id="<?php echo $profile->id; ?>"
							data-profile-desc="<?php echo $profile->get('description');?>"
							data-profile-image-title="<?php echo $this->html('string.escape', $profile->getTitle());?>"
							data-profile-image="<?php echo $profile->getAvatar(SOCIAL_AVATAR_LARGE);?>"
							>
						<?php echo $profile->get('title');?>
					</option>
				<?php } ?>
				</select>

			</div>
			<div class="hide" data-option-template>
				<div data-option-item>
					<div id="es">
						<div class="es-profile-type-dropdown-item">
							<div class="o-media o-media--top">
								<?php if ($this->config->get('registrations.layout.avatar')) { ?>
								<div class="o-media__image">
									<div class="o-avatar-v2 ">
										<div class="o-avatar-v2__content">
											<img data-img>
										</div>
									</div>
								</div>
								<?php } ?>
								<div class="o-media__body">
									<div class="es-profile-type-dropdown-item__title" data-title></div>
									<div data-desc></div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<?php } ?>
		<?php } ?>

		<?php echo $this->html('html.emptyBlock', 'COM_EASYSOCIAL_REGISTRATIONS_NO_PROFILES_CREATED_YET', 'fa-users'); ?>
	</div>
</div>
