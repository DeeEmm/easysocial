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
<?php if (!$invisible) { ?>
<div id="recaptcha_<?php echo $uid;?>">
</div>
<?php } ?>

<?php if ($invisible) { ?>
<div class="g-recaptcha"
	data-sitekey="<?php echo $key;?>"
	data-badge="<?php echo $invisiblePosition;?>"
	data-size="invisible"
	data-es-recaptcha-invisible
	data-callback="getResponse"
></div>
<input type="hidden" name="g-recaptcha-response" value="" data-es-recaptcha-response />
<?php } ?>
