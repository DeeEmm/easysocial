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
<div class="es-stream-apps">
	<div class="es-stream-apps__hd">
		<a href="<?php echo ESR::profile(array('id' => $actor->getAlias(), 'appId' => $app->getAlias()));?>" class="es-stream-apps__title">
			 <i class="fa fa-rss-square"></i>&nbsp; <?php echo $feed->_('title');?>
		</a>
		<div class="es-stream-apps__desc">
			<?php echo $this->html('string.truncate', $feed->description, 350); ?>
		</div>
	</div>
		
</div>