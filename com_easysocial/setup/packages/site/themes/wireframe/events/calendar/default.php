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
<div class="es-events-calendar">
	<div data-es-events-calendar></div>

	<div class="" data-es-events-calendar-loading>
		<div class="o-empty o-empty--bg-shade">
			<div class="o-empty__content">
				<div class="o-loader with-text is-active"><?php echo JText::_('COM_ES_LOADING');?></div>
			</div>
		</div>
	</div>
</div>
