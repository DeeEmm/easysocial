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
<i class="es-side-widget__icon fa fa-birthday-cake t-text--muted t-lg-mr--md"></i>
<?php echo (isset($search) && $search) ? '<a href="' . $search . '" "t-inline-block">' : '';?>
<span class="t-text--muted t-inline-block"><?php echo $birthDate;?>
<?php echo (isset($search) && $search) ? '</a>' : '';?>

<?php if (!empty($age)) { ?>
	<span class="t-text--muted t-inline-block">(<?php echo JText::sprintf('FIELDS_USER_BIRTHDAY_YEARS_OLD', $age);?>)
<?php } ?>
