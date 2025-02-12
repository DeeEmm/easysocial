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
<div class="es-search-mini-result">
	<div class="es-search-mini-result-wrap">
		<div class="es-search-mini-group">
			<div class="es-search-mini-group__title"><?php echo JText::_('COM_ES_SEACH_SUGGESTED_KEYWORDS'); ?></div>
			<div class="es-search-mini-result-list" data-nav-search-ul>
			<?php foreach ($result as $item) { ?>
				<?php echo $this->loadTemplate('site/search/mini/suggestion.item', array('item' => $item)); ?>
			<?php } ?>
			</div>
		</div>
	</div>
</div>
