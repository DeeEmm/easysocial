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
<ul class="g-list-unstyled">
	<?php foreach( $options as $option ) { ?>
	<li>
		<?php echo (isset($option->advancedsearchlink) && $option->advancedsearchlink) ? '<a href="' . $option->advancedsearchlink . '">' : ''; ?>
		<?php echo $option->get('title'); ?>
		<?php echo (isset($option->advancedsearchlink) && $option->advancedsearchlink) ? '</a>' : ''; ?>
	</li>
	<?php } ?>
</ul>
