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
<?php if ($users) { ?>
	<div class="es-side-avatar-lists">
	<?php foreach ($users as $user) { ?>
		<div class="es-side-avatar-lists__item">
			<?php echo $this->html('avatar.user', $user); ?>
		</div>
	<?php } ?>
	</div>
<?php } else { ?>
<div class="t-text--muted">
	<?php echo $emptyMessage; ?>
</div>
<?php } ?>
