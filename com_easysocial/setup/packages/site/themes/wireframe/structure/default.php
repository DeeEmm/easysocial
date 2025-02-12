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
<div id="es" class="es-component es-frontend es-main <?php echo $view . $task . $object . $layout . $suffix; ?> <?php echo $this->responsiveClass();?>" data-es-structure>
	<?php echo $this->render('module', 'es-general-top'); ?>
 
	<?php if ($show !== 'iframe') { ?>
		<?php echo $this->render('module', 'stackideas-toolbar'); ?>
	<?php } ?>

	<?php echo $this->render('module', 'es-general-after-toolbar'); ?>

	<?php echo ES::info()->html(); ?>

	<?php echo $this->render('module', 'es-general-before-contents'); ?>

	<?php echo $contents; ?>

	<?php echo $this->render('module', 'es-general-bottom'); ?>

	<div><?php echo $scripts; ?></div>

	<div data-es-popbox-error style="display:none;"><?php echo JText::_('COM_EASYSOCIAL_POPBOX_ERROR_UNABLE_TO_LOAD_CONTENT'); ?></div>
</div>