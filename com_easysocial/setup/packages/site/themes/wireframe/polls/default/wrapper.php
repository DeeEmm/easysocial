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
<div data-wrapper>
	<?php if ($snackbar) { ?>
	<div class="es-snackbar2">
		<div class="es-snackbar2__context">
			<div class="es-snackbar2__title">
				<?php echo JText::_($snackbar);?>
			</div>
		</div>
	</div>
	<?php } ?>

	<?php echo $this->includeTemplate('site/polls/default/items'); ?>
</div>
