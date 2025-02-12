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
<div class="row" data-edit-privacy>
	<div class="col-md-6">
		<div class="panel">
			<div class="panel-head">
				<b><?php echo JText::_('COM_EASYSOCIAL_PROFILES_PROFILE_PRIVACY_PANEL_TITLE');?></b>
			</div>

			<div class="panel-body">

				<?php if (empty($privacy)) { ?>
					<div class="form-group">
						<label class="col-md-5">
							<?php echo JText::_('COM_EASYSOCIAL_PRIVACY_NOT_FOUND'); ?>
						</label>
					</div>
				<?php } else { ?>
					<div class="form-group">
						<div class="checkbox">
							<label>
								<input type="checkbox" value="1" name="privacyReset" /> <?php echo JText::_('COM_EASYSOCIAL_PRIVACY_RESET_ALL_USER_DESCRIPTION'); ?>
							</label>
						</div>
					</div>

					<?php if ($this->config->get('users.privacy.field')) { ?>
					<div class="form-group">
						<div class="checkbox">
							<?php
								$editLink = '<a href="javascript:void(0);" data-default-field-edit>' . JText::_('COM_ES_PRIVACY_HERE') . '</a>';
								echo JText::sprintf('COM_ES_PRIVACY_GLOBAL_INSTRUCTION', $editLink);
							?>
							<?php
								$defaultPrivacyCustomFields = '';
								if ($profile->privacy_fields) {
									$tmp = ES::json()->decode($profile->privacy_fields);
									$defaultPrivacyCustomFields = implode(',', $tmp);
								}
							?>
							<input type="hidden" data-default-privacy-field name="defaultPrivacyField" value="<?php echo $defaultPrivacyCustomFields; ?>" />
						</div>
					</div>
					<?php } ?>

					<?php
						$index = 0;
						foreach ($privacy->getData() as $key => $groups) {

							// Do not display friends privacy item if friends disabled
							if ($key == 'friends' && !$this->config->get('friends.enabled')) {
								continue;
							}
					?>

						<div class="form-group">
							<div class="col-md-5">
								<h4><?php echo JText::_('COM_EASYSOCIAL_PROFILES_' . strtoupper($key) ); ?></h4>
							</div>
						</div>

						<?php
							foreach ($groups as $item) {
								$gKey = strtoupper($key);
								$rule = str_replace( '.', '_', $item->rule);
								$rule = strtoupper($rule);
								$ruleLangKeys = 'COM_EASYSOCIAL_PROFILES_' . strtoupper($gKey) . '_' . strtoupper($rule);
								$hasCustom = false;
								$hasField = false;
								$isCustom  = false;
								$customIds = '';
								$fields = '';

								if (isset($item->field) && $item->field) {
									$fields = implode(',', $item->field);
								}
						?>

							<div class="form-group privacyItem" data-privacy-item>
								<label class="col-md-6">
									<?php echo JText::_($ruleLangKeys); ?>
									<i class="fa fa-question-circle pull-right"
										<?php echo $this->html('bootstrap.popover', JText::_('COM_EASYSOCIAL_PROFILES_' . $gKey), JText::_($ruleLangKeys), 'bottom'); ?>
									></i>
								</label>
								<div class="col-md-6">
									<div class="o-select-group">
										<select id="privacy_<?php echo $gKey;?>_<?php echo $rule;?>" class="o-form-control privacySelection" name="privacy[<?php echo $gKey;?>][<?php echo $rule;?>]" data-privacy-select>

											<?php
												foreach ($item->options as $option => $value) {

													// we need to remove 'friends' / 'friend of friends' if Friends disabled.
													if (!$this->config->get('friends.enabled') && ($option == SOCIAL_PRIVACY_20 || $option == SOCIAL_PRIVACY_30)) {
														unset($item->options[$option]);

														// set member as default.
														if ($value) {
															$item->options[SOCIAL_PRIVACY_10] = 1;
														}
													}
												}
											?>

											<?php foreach( $item->options as $option => $value) {

												// profiles page shouldnt allow to see this option.
												if( $option == 'custom')
													continue;

												if ($option == 'field') {
													$hasField = true;
													continue;
												}

												$hasCustom = ($option == 'custom' && $value) ? true : false;

											?>
												<option value="<?php echo $option?>" <?php echo ($value) ? 'selected="selected"': ''?> ><?php echo JText::_('COM_EASYSOCIAL_PRIVACY_OPTION_' . strtoupper($option)); ?></option>
											<?php } ?>
										</select>

										<label for="privacy_<?php echo $gKey;?>_<?php echo $rule;?>" class="o-select-group__drop"></label>
									</div>

									<?php if ($this->config->get('users.privacy.field') && $hasField) { ?>
										<a href="javascript:void(0);" data-privacy-field
											data-es-provide="popover"
											data-content="<?php echo JText::_('COM_ES_PRIVACY_TIPS_CUSTOM_FIELD_DESC'); ?>"
											data-title="<?php echo JText::_('COM_ES_PRIVACY_TIPS_CUSTOM_FIELD_TITLE'); ?>"
											data-placement="top">
											<?php echo JText::_('COM_ES_PRIVACY_EDIT'); ?>
										</a>
									<?php } ?>

									<input type="hidden" name="privacyID[<?php echo $gKey;?>][<?php echo $rule;?>]" value="<?php echo $item->id . '_' . $item->mapid; ?>" />
									<input type="hidden" data-hidden-custom name="privacyCustom[<?php echo $gKey;?>][<?php echo $rule; ?>]" value="<?php echo $customIds; ?>" />
									<input type="hidden" data-hidden-field name="privacyField[<?php echo $gKey;?>][<?php echo $rule; ?>]" value="<?php echo $fields; ?>" />
								</div>
							</div>
						<?php } ?>

						<?php if ($key == 'profiles') { ?>
							<div class="form-group">
								<label class="col-md-6">
									<?php echo JText::_('COM_ES_PROFILE_ROBOTS_OPTIONS'); ?>
									<i class="fa fa-question-circle pull-right" <?php echo $this->html('bootstrap.popover', JText::_('COM_ES_PROFILE_ROBOTS_OPTIONS'), JText::_('COM_ES_PROFILE_ROBOTS_OPTIONS_DESC'), 'bottom'); ?>></i>
								</label>
								<div class="col-md-6">
									<?php echo $this->html('grid.selectlist', 'params[robots]', $param->get('robots'), [
										['value' => SOCIAL_PROFILE_ROBOTS_INHERIT, 'text' => JText::_('COM_ES_PROFILE_ROBOTS_INHERIT')],
										['value' => SOCIAL_PROFILE_ROBOTS_INDEX, 'text' => JText::_('COM_ES_PROFILE_ROBOTS_INDEX')],
										['value' => SOCIAL_PROFILE_ROBOTS_NOINDEX, 'text' => JText::_('COM_ES_PROFILE_ROBOTS_NOINDEX')]
									]); ?>
								</div>
							</div>
						<?php } ?>
					<?php } ?>
				<?php } ?>
			</div>
		</div>
	</div>
</div>
