<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div class="es-container <?php echo !$feeds ? ' is-empty' : '';?>" data-es-container data-feeds data-uid="<?php echo $cluster->id;?>" data-app="<?php echo $appId;?>">

	<?php echo $this->html('html.sidebar'); ?>

	<?php if ($this->isMobile()) { ?>
		<?php echo $this->output('site/feeds/default/mobile'); ?>
	<?php } ?>

	<div class="es-content">
		<div class="app-contents">
			<?php echo $this->html('html.loading'); ?>
			<?php echo $this->html('html.emptyBlock', 'APP_FEEDS_NO_FEED_YET', 'fa-rss'); ?>

			<div data-feeds-lists>
				<?php foreach ($feeds as $feed) { ?>
					<?php echo $this->loadTemplate('site/feeds/default/item', array('cluster' => $cluster, 'feed' => $feed, 'totalDisplayed' => $totalDisplayed, 'user' => $user)); ?>
				<?php } ?>
			</div>
		</div>
	</div>
</div>
