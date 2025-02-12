<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div class="es-story-broadcast-form" data-story-broadcast-form>
	<div class="o-form-group">
		<input type="text" name="title" class="o-form-control" data-broadcast-title placeholder="<?php echo JText::_('APP_BROADCAST_STORY_FORM_SET_TITLE_PLACEHOLDER');?>" />
	</div>

	<div class="o-form-group">
		<input type="text" name="broadcast_link" class="o-form-control" data-broadcast-link placeholder="<?php echo JText::_('APP_BROADCAST_STORY_FORM_SET_LINK_PLACEHOLDER');?>" />
	</div>

	<div class="o-form-group">
		<textarea autocomplete="off" class="o-form-control" placeholder="<?php echo JText::_('APP_BROADCAST_STORY_FORM_SET_CONTENT_PLACEHOLDER'); ?>" data-broadcast-message></textarea>
	</div>

	<div class="o-grid o-grid--gutters">
		<?php if ($groupEnabled) { ?>
		<div class="o-grid__cell">
			<select name="broadcast_context" class="o-form-control" data-broadcast-context>
				<option value="profile"><?php echo JText::_('APP_BROADCAST_STORY_FORM_SET_CONTEXT_PROFILE'); ?></option>
				<option value="group"><?php echo JText::_('APP_BROADCAST_STORY_FORM_SET_CONTEXT_GROUP'); ?></option>
			</select>
		</div>
		<?php } else { ?>
			<input type="hidden" name="broadcast_context" data-broadcast-context value="profile" />
		<?php } ?>

		<div class="o-grid__cell">
			<select name="broadcast_type" class="o-form-control" data-broadcast-type>
				<option value="popup"><?php echo JText::_('APP_BROADCAST_STORY_FORM_SET_TYPE_POPUP'); ?></option>
				<option value="notification"><?php echo JText::_('APP_BROADCAST_STORY_FORM_SET_TYPE_NOTIFICATION'); ?></option>
			</select>
		</div>
		<div class="o-grid__cell">
			<select name="broadcast_profile" class="o-form-control" data-broadcast-send-type>
				<option value="all"><?php echo JText::_('APP_BROADCAST_STORY_FORM_SET_PROFILES_ALL'); ?></option>
				<option value="selected"><?php echo JText::_('APP_BROADCAST_STORY_FORM_SET_PROFILES_SELECTED'); ?></option>
			</select>
		</div>
	</div>

	<div class="o-form-group" data-broadcast-multilist>
		<select name="broadcast_list" multiple class="o-form-control" data-broadcast-send-list>
		</select>
	</div>

	<div class="o-form-group">
		<div id="datetimepicker4" class="o-input-group" data-broadcast-expirydate data-value>
			<input type="text" class="o-form-control" placeholder="<?php echo JText::_('COM_EASYSOCIAL_POLLS_EXPIRED_DATE'); ?>" data-picker data-datetime-format="<?php echo $params->get('datetime_format');?>" />
			<input type="hidden" data-datetime />
			<span class="o-input-group__addon" data-picker-toggle>
				<i class="far fa-calendar-alt"></i>
			</span>
		</div>
	</div>
</div>
