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
<div class="es-container<?php echo !$feeds ? ' is-empty' : '';?>" data-es-container data-feeds>

	<?php echo $this->html('html.sidebar'); ?>

	<?php if ($this->isMobile()) { ?>
		<?php echo $this->output('apps/user/feeds/profile/mobile'); ?>
	<?php } ?>

	<div class="es-content">
		<div data-feeds-list>
			<?php if ($feeds) { ?>
				<?php foreach ($feeds as $feed) { ?>
					<?php echo $this->output('apps/user/feeds/profile/item', array('feed' => $feed, 'user' => $user)); ?>
				<?php } ?>
			<?php } ?>
		</div>

		<?php echo $this->html('html.emptyBlock', 'APP_FEEDS_NO_FEED_YET', 'fa-rss-square'); ?>
	</div>
</div>
