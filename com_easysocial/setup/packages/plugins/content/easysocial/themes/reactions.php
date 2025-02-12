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
<div id="es"  class="t-lg-mb--md es <?php echo ES::responsive()->isMobile() ? 'is-mobile' : 'is-desktop';?>" data-stream-actions>
	<div class="es-reactions-btn t-lg-mb--md">
		<?php echo $likes->button(true);?>
	</div>

	<div class="es-reactions-blk">
		<?php echo $likes->html();?>
	</div>
</div>
