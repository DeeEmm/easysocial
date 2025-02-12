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
<div class="es-hints" data-hints-friends>
	<div data-search>
		<span class="mentions-autocomplete-search-hint"><?php echo ($this->config->get('friends.enabled')) ? JText::_('COM_EASYSOCIAL_FRIENDS_SUGGEST_HINT_SEARCH') : JText::_('COM_EASYSOCIAL_FRIENDS_SUGGEST_HINT_SEARCH_NON_FRIEND'); ?></span>
	</div>

	<div data-empty>
		<span class="mentions-autocomplete-empty-text"><?php echo ($this->config->get('friends.enabled')) ? JText::_('COM_EASYSOCIAL_FRIENDS_SUGGEST_HINT_EMPTY') : JText::_('COM_EASYSOCIAL_FRIENDS_SUGGEST_HINT__NON_FRIEND_EMPTY') ; ?></span>
	</div>
</div>
