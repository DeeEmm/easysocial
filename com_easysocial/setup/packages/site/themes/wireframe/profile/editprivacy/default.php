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
<div class="es-container" data-es-container data-edit-privacy>

	<?php echo $this->html('html.sidebar'); ?>

	<?php if ($this->isMobile()) { ?>
		<?php echo $this->includeTemplate('site/profile/editprivacy/mobile.filters'); ?>
	<?php } ?>

	<div class="es-content">
		<?php echo $this->render('module', 'es-profile-editprivacy-before-contents'); ?>

		<form method="post" action="<?php echo JRoute::_('index.php');?>" class="es-forms">

			<div class="tab-content">
				<?php if ($privacy) { ?>
					<?php $i = 0; ?>
					<?php foreach ($privacy as $group) { ?>
					<div class="tab-content__item <?php echo ($i == 0 && !$activeTab) || ($activeTab && $activeTab == $group->element) ? 'is-active' : '';?>" data-contents data-type="<?php echo $group->element; ?>">
						<div class="es-forms__group">
							<div class="es-forms__title">
								<?php echo $this->html('form.title', $group->title); ?>
							</div>

							<div class="es-forms__content">
								<p class="privacy-contents__title">
									<?php echo $group->description;?>
								</p>

								<div class="o-form-horizontal">
								<?php foreach ($group->items as $item) { ?>
									<?php if (!$item) { continue; } ?>
									<div class="o-form-group" data-privacy-item>
										<?php echo $this->html('form.label', $item->label, 3, true, $item->tips); ?>

										<div class="o-control-input">

											<select name="privacy[<?php echo $item->groupKey;?>][<?php echo $item->rule;?>]" class="o-form-control privacySelection" autocomplete="off" data-privacy-select>
												<?php foreach ($item->options as $option => $value) { ?>
													<?php
														if ($option == 'field') {
															// User are not allow to pre-configure custom field privacy.
															continue;
														}
													?>
													<option value="<?php echo $option;?>"<?php echo $value ? ' selected="selected"' : '';?>>
														<?php echo JText::_('COM_EASYSOCIAL_PRIVACY_OPTION_' . strtoupper($option));?>
													</option>
												<?php } ?>
											</select>

											<div class="es-privacy-contents-custom">
												<a href="javascript:void(0);" class="t-text--muted" style="<?php echo !$item->hasCustom ? 'display:none;' : '';?>" data-privacy-custom-edit-button>
													<i class="fa fa-cog"></i>
												</a>

												<div class="dropdown-menu dropdown-menu-left privacy-custom-menu" style="display:none;" data-privacy-custom-form>
													<div class="t-lg-mb--md ">
														<div class="">
															<?php echo JText::_('COM_EASYSOCIAL_PRIVACY_CUSTOM_DIALOG_NAME'); ?>
															<a href="javascript:void(0);" class="pull-right" data-privacy-custom-hide-button>
																<i class="fa fa-times " title="<?php echo JText::_('COM_EASYSOCIAL_PRIVACY_CUSTOM_DIALOG_HIDE' , true );?>"></i>
															</a>
														</div>
													</div>
													<div class="textboxlist" data-textfield>
														<?php if ($item->hasCustom) { ?>
															<?php foreach ($item->customUsers as $friend) { ?>
															<div class="textboxlist-item" data-id="<?php echo $friend->id; ?>" data-title="<?php echo $friend->getName(); ?>" data-textboxlist-item>
																<span class="textboxlist-itemContent" data-textboxlist-itemContent><?php echo $friend->getName(); ?><input type="hidden" name="items" value="<?php echo $friend->id; ?>" /></span>
																<a class="textboxlist-itemRemoveButton" href="javascript: void(0);" data-textboxlist-itemRemoveButton></a>
															</div>
															<?php } ?>
														<?php } ?>
														<input type="text" class="textboxlist-textField" data-textboxlist-textField placeholder="<?php echo JText::_('COM_EASYSOCIAL_PRIVACY_CUSTOM_DIALOG_ENTER_NAME'); ?>" autocomplete="off" />
													</div>
												</div>
											</div>



											<input type="hidden" name="privacyID[<?php echo $item->groupKey;?>][<?php echo $item->rule; ?>]" value="<?php echo $item->id . '_' . $item->mapid;?>" />
											<input type="hidden" name="privacyOld[<?php echo $item->groupKey;?>][<?php echo $item->rule; ?>]" value="<?php echo $item->selected; ?>" />
											<input type="hidden" data-hidden-custom name="privacyCustom[<?php echo $item->groupKey;?>][<?php echo $item->rule; ?>]" value="<?php echo implode(',', $item->customIds); ?>" />
										</div>
									</div>
								<?php } ?>

								<?php if ($group->element == 'profiles') { ?>
									<div class="o-form-group">
										<?php echo $this->html('form.label', JText::_('COM_ES_PROFILE_ROBOTS_OPTIONS'), 3, true, JText::_('COM_ES_PROFILE_ROBOTS_OPTIONS_DESC')); ?>
										<div class="o-control-input">
											<?php echo $this->html('grid.selectlist', 'searchIndexing', $robots, array(
												array('value' => SOCIAL_PROFILE_ROBOTS_INHERIT, 'text' => JText::_('COM_ES_PROFILE_ROBOTS_INHERIT')),
												array('value' => SOCIAL_PROFILE_ROBOTS_INDEX, 'text' => JText::_('COM_ES_PROFILE_ROBOTS_INDEX')),
												array('value' => SOCIAL_PROFILE_ROBOTS_NOINDEX, 'text' => JText::_('COM_ES_PROFILE_ROBOTS_NOINDEX'))
											)); ?>
										</div>
									</div>
								<?php } ?>
								</div>
							</div>
						</div>
					</div>
					<?php $i++;?>
					<?php } ?>
				<?php } ?>
			</div>

			<div class="es-forms__actions">
				<div class="o-form-actions" data-form-actions>
					<button class="btn btn-es-primary t-lg-pull-right" data-profile-notifications-save><?php echo JText::_('COM_EASYSOCIAL_SAVE_BUTTON');?></button>

					<div class="t-lg-pull-right t-lg-mr--xl">
						<div class="o-checkbox">
							<input type="checkbox" value="1" name="privacyReset" id="reset-privacy" />
							<label for="reset-privacy">
								 <?php echo JText::_('COM_EASYSOCIAL_PRIVACY_RESET_DESCRIPTION'); ?>
							</label>
						</div>
					</div>
				</div>
			</div>

			<?php echo $this->html('form.action', 'profile', 'savePrivacy'); ?>
			<input type="hidden" name="activeTab" value="<?php echo $activeTab;?>" data-privacy-active />
		</form>

		<?php echo $this->render('module', 'es-profile-editprivacy-after-contents'); ?>
	</div>
</div>

