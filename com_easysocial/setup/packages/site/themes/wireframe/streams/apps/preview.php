<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div class="es-stream-embed is-apps">
	<?php echo $this->html('avatar.mini', $app->getAppTitle(), '', '', 'default', 'es-app-item__avatar', '', false, 'square', $app->getTextAvatar()); ?>

	<div class="es-stream-embed__apps-context">
		<div class="es-stream-embed__apps-title">
			<?php echo $app->_('title'); ?> <span><?php echo $app->getMeta()->version;?></span>
		</div>
		<b><?php echo $app->getUserDesc();?></b>
	</div>

</div>