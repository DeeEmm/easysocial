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
<div class="es-profile" data-es-event>

	<?php echo $this->html('cover.event', $event, $layout); ?>

	<div class="es-container" data-es-container>

		<?php echo $this->html('html.sidebar'); ?>

		<?php if ($this->isMobile()) { ?>
			<?php echo $this->includeTemplate('site/events/about/mobile'); ?>
		<?php } ?>

		<div class="es-content">

			<?php echo $this->render('module', 'es-events-about-before-contents'); ?>

			<div class="es-profile-info">
				<?php if ($steps) { ?>
					<?php echo $this->output('site/fields/about/default', array('steps' => $steps, 'canEdit' => $event->isAdmin(), 'objectId' => $event->id, 'routerType' => 'events', 'item' => $event)); ?>
				<?php } ?>
			</div>

			<?php echo $this->render('module', 'es-events-about-after-contents'); ?>
		</div>
	</div>
</div>
