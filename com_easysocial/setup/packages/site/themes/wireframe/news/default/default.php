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
<div class="es-container" data-es-container data-es-news data-id="<?php echo $cluster->id;?>">

	<?php echo $this->html('html.sidebar'); ?>

	<?php if ($this->isMobile()) { ?>
		<?php echo $this->output('site/news/default/mobile'); ?>
	<?php } ?>

	<div class="es-content">
		<div class="app-contents<?php echo !$items ? ' is-empty' : '';?>" data-news-wrapper>
			<?php echo $this->html('html.loading'); ?>
			<?php echo $this->html('html.emptyBlock', 'APP_GROUP_NEWS_EMPTY', 'fa-database'); ?>

			<div data-news-contents>
				<?php foreach ($items as $news) { ?>
					<?php echo $this->loadTemplate('site/news/default/items', array('news' => $news, 'params' => $params, 'cluster' => $cluster)); ?>
				<?php } ?>

				<?php echo $pagination->getListFooter('site'); ?>
			</div>
		</div>
	</div>
</div>
