<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div class="es-restricted-message o-box">
	<div>
		<div class="o-grid">
			<?php if ($icon) { ?>
			<div class="o-grid__cell o-grid__cell--auto-size t-lg-mr--xl">
				<a href="javascript:void(0);" class="t-font-weight--bold">
					<i class="fa fa-lock"></i>
				</a>
			</div>
			<?php } ?>

			<div class="o-grid__cell">
				<a href="javascript:void(0);" class="t-font-weight--bold">
					 <?php echo JText::_($title);?>
				</a>

				<div><?php echo JText::_($content); ?></div>

				<?php echo $customHtml;?>
			</div>
		</div>
	</div>
</div>
