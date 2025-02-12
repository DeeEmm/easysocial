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
defined( '_JEXEC' ) or die( 'Unauthorized Access' );
$esconfig = ES::config();
?>
<div class="form-privacy row" data-edit-privacy>
	<div class="col-lg-7">
		<div class="panel">
			<div class="panel-body">
				<div class="t-fs--sm">
					<div class="es-checkbox">
						<input type="checkbox" value="1" name="privacyReset" id="privacyReset"/>
						<label for="privacyReset">
							<?php echo JText::_( 'COM_EASYSOCIAL_PRIVACY_RESET_USER_DESCRIPTION' ); ?>
						</label>
					</div>
				</div>
			</div>
		</div>
		<?php foreach ($privacy as $group => $items) { ?>
			<?php

				// var_dump($group);
				// photos / albums
				if (($group == 'albums' || $group == 'photos') && !$esconfig->get('photos.enabled')) {
					continue;
				}

				// Only display videos privacy if videos is enabled.
				if ($group == 'videos' && !$esconfig->get('video.enabled')) {
					continue;
				}

				// badges / achievements
				if (($group == 'achievements') && !$esconfig->get('badges.enabled')) {
					continue;
				}

				// followers
				if (($group == 'followers') && !$esconfig->get('followers.enabled')) {
					continue;
				}

				// Do not display friends privacy item if friends disabled
				if ($group == 'friends' && !$esconfig->get('friends.enabled')) {
					continue;
				}

				// Do not display points privacy item
				if ($group == 'points' && !$this->config->get('points.enabled')) {
					continue;
				}

				// Do not display application privacy item
				if ($group == 'apps' && !$this->config->get('apps.browser')) {
					continue;
				}

				// Do not display polls privacy item
				if ($group == 'polls' && !$this->config->get('polls.enabled')) {
					continue;
				}

			?>
			<div class="panel">
				<div class="panel-head">
					<b><?php echo JText::_( 'COM_EASYSOCIAL_PRIVACY_GROUP_' . strtoupper( $group ) ); ?></b>
				</div>
				<div class="panel-body">
					<?php

					foreach( $items as $item ) {

						// profile - conversation
						if (($group == 'profiles' && $item->rule == 'post.message') && !$esconfig->get('conversations.enabled')) {
							continue;
						}

						// if friends disabled and the default value set to 'friends', lets override it to 'member'
						if (!$esconfig->get('friends.enabled') && ($item->default == SOCIAL_PRIVACY_FRIENDS_OF_FRIEND || $item->default == SOCIAL_PRIVACY_FRIEND)) {
							$item->default = SOCIAL_PRIVACY_MEMBER;
						}

						$hasCustom = ( $item->custom ) ? true : false;
						$customIds = '';
						$curValue  = '';
					?>
					<div class="form-group">
						<label class="col-md-5 control-label">
							<?php echo $item->label; ?>
							<i class="fa fa-question-circle t-lg-pull-right" <?php echo $this->html( 'bootstrap.popover' , $item->label , $item->tips , 'bottom' ); ?>></i>
						</label>

						<div class="col-md-7" data-privacy-item>
							<div class="o-select-group">
								<select id="privacy_<?php echo $item->groupKey;?>_<?php echo $item->rule;?>" name="privacy[<?php echo $item->groupKey;?>][<?php echo $item->rule;?>]" data-privacy-select autocomplete="off" class="o-form-control  privacySelection">
									<?php

										foreach( $item->options as $option => $value ) {

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

									<?php foreach( $item->options as $option => $value ){

										if ($option == 'field') {
											// user are not allow to pre-configure custom field privacy.
											continue;
										}

										if ($value) {
											$curValue = $option;
										}

										if ($this->config->get( 'general.site.lockdown.enabled' ) && $option == SOCIAL_PRIVACY_0) {
											continue;
										}
									?>
										<option value="<?php echo $option;?>"<?php echo $value ? ' selected="selected"' : '';?>>
											<?php echo JText::_( 'COM_EASYSOCIAL_PRIVACY_OPTION_' . strtoupper( $option ) );?>
										</option>
									<?php } ?>
								</select>

								<label for="privacy_<?php echo $item->groupKey;?>_<?php echo $item->rule;?>" class="o-select-group__drop"></label>
							</div>

							<a <?php if( !$hasCustom ) { ?>style="display: none;"<?php } ?> href="javascript:void(0);" data-privacy-custom-edit-button>
								<i class="icon-es-settings"></i>
							</a>

							<div data-privacy-custom-form
								class="dropdown-menu dropdown-arrow-topleft privacy-custom-menu"
								style="display:none;"
							>
								<div class="row-fluid">
									<?php echo JText::_('COM_EASYSOCIAL_PRIVACY_CUSTOM_DIALOG_NAME'); ?>
									<a href="javascript:void(0);" class="pull-right" data-privacy-custom-hide-button>
										<i class="fa fa-times" title="<?php echo JText::_('COM_EASYSOCIAL_PRIVACY_CUSTOM_DIALOG_HIDE' , true );?>"></i>
									</a>
								</div>
								<div class="textboxlist" data-textfield >

									<?php
										if( $hasCustom )
										{
											foreach( $item->custom as $friend )
											{
												if( $customIds )
												{
													$customIds = $customIds . ',' . $friend->user_id;
												}
												else
												{
													$customIds = $friend->user_id;
												}

												$friend = ES::user( $friend->user_id );
									?>
										<div class="textboxlist-item" data-id="<?php echo $friend->id; ?>" data-title="<?php echo $friend->getName(); ?>" data-textboxlist-item>
											<span class="textboxlist-itemContent" data-textboxlist-itemContent><?php echo $friend->getName(); ?><input type="hidden" name="items" value="<?php echo $friend->id; ?>" /></span>
											<a class="textboxlist-itemRemoveButton" href="javascript: void(0);" data-textboxlist-itemRemoveButton></a>
										</div>
									<?php
											}

										}
									?>

									<input type="text" class="textboxlist-textField" data-textboxlist-textField placeholder="<?php echo JText::_('COM_EASYSOCIAL_PRIVACY_CUSTOM_DIALOG_ENTER_NAME'); ?>" autocomplete="off" />
								</div>
							</div>

							<input type="hidden" name="privacyID[<?php echo $item->groupKey;?>][<?php echo $item->rule; ?>]" value="<?php echo $item->id . '_' . $item->mapid;?>" />
							<input type="hidden" name="privacyOld[<?php echo $item->groupKey;?>][<?php echo $item->rule; ?>]" value="<?php echo $curValue; ?>" />
							<input type="hidden" data-hidden-custom name="privacyCustom[<?php echo $item->groupKey;?>][<?php echo $item->rule; ?>]" value="<?php echo $customIds; ?>" />
						</div>
					</div>
					<?php } ?>

					<?php if ($group == 'profiles') { ?>
					<div class="form-group">
						<label class="col-md-5 control-label">
						 <?php echo JText::_('COM_ES_PROFILE_ROBOTS_OPTIONS'); ?>
						 <i class="fa fa-question-circle t-lg-pull-right" <?php echo $this->html('bootstrap.popover', JText::_('COM_ES_PROFILE_ROBOTS_OPTIONS'), JText::_('COM_ES_PROFILE_ROBOTS_OPTIONS_DESC'), 'bottom'); ?>></i>
						</label>
						<div class="col-md-7">
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
		<?php } ?>
	</div>
</div>
