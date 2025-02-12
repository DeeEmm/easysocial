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
<input type="text" class="o-form-control app-filter-bar__search-input" name="search" value="<?php echo $this->html('string.escape', $value);?>" placeholder="<?php echo JText::_('COM_EASYSOCIAL_SEARCH', true);?>" data-table-grid-search-input />
<span class="app-filter-bar__search-btn-group">

	<button type="button" class="btn app-filter-bar__search-btn <?php echo !$value ? 't-hidden' : '';?>" data-table-grid-search-reset>
		<i class="fa fa-times"></i>
	</button>

	<button type="button" class="btn btn-es-default-o app-filter-bar__search-btn" data-table-grid-search>
		<i class="fa fa-search"></i>
	</button>
</span>
