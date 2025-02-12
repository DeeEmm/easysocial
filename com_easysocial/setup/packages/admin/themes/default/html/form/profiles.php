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
<?php if (!$multiple) { ?>
<div class="o-select-group">
<?php } ?>

	<select name="<?php echo $name;?>" id="<?php echo !$id ? $name : $id; ?>" <?php echo $multiple ? ' multiple="multiple"' : '';?> <?php echo $attributes; ?> class="o-form-control" autocomplete="off"
		<?php echo $multiple ? 'style="min-height: 150px;"' : '';?>>
		<?php if ($default) { ?>
		<option value="default" <?php echo $selected == 'default' ? 'selected="selected"' : '';?>><?php echo JText::_('COM_EASYSOCIAL_PROFILES_USE_CURRENT_DEFAULT_PROFILE');?></option>
		<?php } ?>

		<?php foreach ($profiles as $profile) { ?>
			<option value="<?php echo $profile->id; ?>"<?php echo $profile->id == $selected || (is_array($selected) && in_array($profile->id, $selected)) ? ' selected="selected"' : '';?>><?php echo $profile->_('title'); ?></option>
		<?php } ?>
	</select>

<?php if (!$multiple) { ?>
	<label for="<?php echo !$id ? $name : $id; ?>" class="o-select-group__drop"></label>
</div>
<?php } ?>
