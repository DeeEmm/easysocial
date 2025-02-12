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
<div class="es-field-header">
	<?php echo $this->html('form.title', $field->_('title'), 'h2'); ?>
	
	<?php if ($field->display_description && $field->description) { ?>
	<p><?php echo $field->_('description'); ?></p>
	<?php } ?>
</div>