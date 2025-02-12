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
<div class="es-stream-embed is-link">
	<?php if ($article->image) { ?>
	<a href="<?php echo $article->permalink;?>" class="es-stream-embed__cover">
		<div class="es-stream-embed__cover-img" style="background-image: url('<?php echo $article->image;?>');"></div>
	</a>
	<?php } ?>

	<a href="<?php echo $article->permalink;?>" class="es-stream-embed__title <?php echo $article->image ? ' es-stream-embed--border' : '';?>">
		 <?php echo $article->title;?>
	</a>
	<div class="es-stream-embed__meta">
		<ul class="g-list-inline g-list-inline--space-right t-text--muted">
			<li>
				<i class="fa fa-calendar"></i>&nbsp; <?php echo $article->date->format(JText::_('COM_EASYSOCIAL_DATE_DMY'));?>
			</li>
			<li>
				<a href="<?php echo $article->categoryPermalink;?>">
					<i class="fa fa-folder"></i>&nbsp; <?php echo $article->category->title;?>
				</a>
			</li>
		</ul>
	</div>

	<div class="es-stream-embed__desc">
		<?php echo $article->content; ?>
	</div>

	<div class="es-stream-embed__desc">
		<a href="<?php echo $article->permalink;?>"><?php echo JText::_('APP_ARTICLE_CONTINUE_READING'); ?> &rarr;</a>
	</div>
</div>
