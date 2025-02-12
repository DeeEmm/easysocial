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
<?php if ($invisible) { ?>
<style type="text/css">
div[data-id="<?php echo $field->id;?>"] .o-control-label,
div[data-id="<?php echo $field->id;?>"] .o-help-block {
	display: none !important;
}

div[data-id="<?php echo $field->id;?>"] .g-recaptcha {
	margin-left: 25%;
}
</style>
<?php } ?>

<div data-field-recaptcha>
	<?php echo $captcha->html($field->id, $invisible);?>
</div>
