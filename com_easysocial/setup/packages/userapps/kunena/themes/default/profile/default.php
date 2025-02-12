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
<div class="es-container" data-es-container>

	<?php echo $this->html('html.sidebar'); ?>

	<?php if ($this->isMobile()) { ?>
		<?php echo $this->output('apps:/user/kunena/themes/default/profile/mobile'); ?>
	<?php } ?>

	<div class="es-content">
		<div class="app-kunena <?php echo !$posts ? 'is-empty' : '';?>">
			<?php if ($posts) { ?>
				<?php foreach ($posts as $topic) { ?>
					<?php echo $this->loadTemplate('apps:/user/kunena/themes/default/profile/item', [
						'topic' => $topic, 
						'kunenaTemplate' => $kunenaTemplate,
						'kunenaHelper' => $kunenaHelper
					]); ?>
				<?php } ?>
			<?php } ?>

			<?php echo $this->html('html.emptyBlock', JText::sprintf('APP_KUNENA_EMPTY_POSTS', $user->getName()), 'fa-info-circle'); ?>
		</div>
	</div>
</div>
