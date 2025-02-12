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
<div class="es-container" data-es-exports data-es-container>

	<div class="es-content">
		<?php echo $this->render('module', 'es-exports-before-contents'); ?>

		<div data-contents>
			<div class="is-empty">
				<?php echo $this->html('html.emptyBlock', 'COM_ES_EXPORT_NO_DATA_FOUND', 'fa-bullseye'); ?>
			</div>
		</div>

		<?php echo $this->render('module', 'es-exports-after-contents'); ?>
	</div>
</div>
