<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2016 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div id="es" class="es mod-es-oauth module-social<?php echo $lib->getSuffix();?>">
<?php if ($sso->hasSocialButtons()) { ?>
	<?php foreach ($sso->getSocialButtons() as $social => $button) { ?>
	<div class="es-signin-<?php echo $social; ?>">
		<?php echo $button; ?>
	</div>
	<?php } ?>
<?php } ?>
</div>