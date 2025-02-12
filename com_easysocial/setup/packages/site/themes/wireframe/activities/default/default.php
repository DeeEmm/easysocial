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
<div class="es-container" data-es-container data-activities>

	<?php echo $this->html('html.sidebar'); ?>

	<?php if ($this->isMobile()) { ?>
		<?php echo $this->includeTemplate('site/activities/default/mobile'); ?>
	<?php } ?>

	<div class="es-content" data-wrapper>
		<?php echo $this->html('html.loading'); ?>

		<?php echo $this->render('module', 'es-activities-before-contents'); ?>

		<div class="<?php echo !$activities ? 'is-empty': ''; ?>" data-contents>
			<?php echo $this->includeTemplate('site/activities/default/content', array('filtertype' => $filtertype)); ?>
		</div>

		<?php echo $this->render('module', 'es-activities-after-contents'); ?>
	</div>
</div>
