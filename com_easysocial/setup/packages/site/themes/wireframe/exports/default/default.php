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

			<div class="es-snackbar2">
				<div class="es-snackbar2__context">
					<div class="es-snackbar2__title">
						<?php echo JText::_($pageHeader);?>
					</div>
				</div>
			</div>

			<div class="table-responsive">
				<table class="table table-bordered">
					<tr>
					<?php
						// generating header
						$header = array_shift($data);
						$colCount = count($header);

						for($i = 0; $i < $colCount; $i++) {
							echo '<th>' . $header[$i] . '</th>';
						}
					?>
					</tr>

					<?php
						// generating table rows
						foreach ($data as $row) {
							echo $this->loadTemplate('site/exports/default/item', array('row' => $row));
						}
					?>

				</table>
			</div>

			<?php if ($pagination) { ?>
				<?php echo $pagination->getListFooter('site');?>
			<?php } ?>
		</div>

		<?php echo $this->render('module', 'es-exports-after-contents'); ?>
	</div>
</div>
