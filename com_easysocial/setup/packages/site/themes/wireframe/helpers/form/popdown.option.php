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
<?php if (!$selected) { ?>
<a href="<?php echo $url;?>" style="<?php echo $increasedPadding ? 'padding: 12px 20px;' : '';?>" <?php echo $attributes;?>>
<?php } ?>
	<b>
		<?php if ($icon) { ?>
		<i class="fa <?php echo $icon;?>"></i>&nbsp;
		<?php } ?>
		<?php echo $title; ?>
	</b>
	<div class="dropdown-menu--popdown__desp" style="white-space:pre-line;"><?php echo $description;?></div>

<?php if (!$selected) { ?>
</a>
<?php } ?>
