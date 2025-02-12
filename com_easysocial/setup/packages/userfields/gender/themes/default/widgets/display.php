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
<?php if ($search) { ?>
<a href="<?php echo $search;?>" class="t-text--muted">
<?php } ?>

<?php if ($value != '1' && $value != '2') { ?>
	<i class="es-side-widget__icon fa fa-venus-mars t-text--muted t-lg-mr--md"></i>

	<span>
		<?php echo $displayValue; ?>
	</span>
<?php } else { ?>
	<i class="es-side-widget__icon fa fa-<?php echo $value == 1 ? 'male' : 'female';?> t-text--muted t-lg-mr--md"></i>

	<span>
		<?php if ($value == 1) { ?>
			<?php echo JText::_('PLG_FIELDS_USER_MALE'); ?>
		<?php } else { ?>
			<?php echo JText::_('PLG_FIELDS_USER_FEMALE'); ?>
		<?php } ?>
	</span>
<?php } ?>


<?php if ($search) {?>
</a>
<?php } ?>
