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
<?php if ($scriptTag) { ?>
<script>
<?php } ?>

<?php if ($cdata) { ?>
//<![CDATA[
<?php } ?>

<?php if ($safeExecution) { ?>
try {
	<?php echo $script; ?>
} catch(e) {
	console.error('An error occured while executing <?php echo $file ?>.', e);
};
<?php } else { ?>
	<?php echo $script; ?>
<?php } ?>

<?php if ($cdata) { ?>
//]]>
<?php } ?>

<?php if ($scriptTag) { ?>
</script>
<?php } ?>
