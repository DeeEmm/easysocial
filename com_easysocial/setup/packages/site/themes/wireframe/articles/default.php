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
<div class="es-container" data-article>

	<?php echo $this->html('html.sidebar'); ?>

	<?php if ($this->isMobile()) { ?>
		<?php echo $this->output('site/articles/mobile'); ?>
	<?php } ?>

	<div class="es-content">
		<div class="app-article">
			<div class="app-contents<?php echo !$articles ? ' is-empty' : '';?>" data-article-lists>
				<?php if ($articles) { ?>
					<?php foreach ($articles as $article) { ?>
						<?php echo $this->output('site/articles/item', array('article' => $article, 'user' => $user, 'maxContentLength' => $maxContentLength)); ?>
					<?php } ?>
				<?php } ?>

				<?php echo $pagination->getListFooter('site');?>

				<?php echo $this->html('html.emptyBlock', JText::_('COM_EASYSOCIAL_NO_ARTICLES_CREATED_YET'), 'fa-database'); ?>
			</div>
		</div>
	</div>
</div>
