<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div class="es-apps-item es-island" data-article-list-item>
	<div class="es-apps-item__hd">
		<a href="<?php echo $article->permalink; ?>" class="es-apps-item__title"><?php echo $article->title; ?></a>
	</div>
	<div class="es-apps-item__bd">
		<div class="es-apps-item__desc">
			<?php if (isset($article->image) && $article->image) { ?>
				<a href="<?php echo $article->permalink;?>" class="blog-image t-lg-pull-right t-lg-ml--xl t-lg-mb--xl">
					<img src="<?php echo $article->image; ?>" align="right" width="220" />
				</a>
			<?php } ?>
			<?php echo $this->html('string.truncate', $article->content, $maxContentLength, '', false, false); ?>
		</div>
		<div class="es-apps-item__item-action">
			<a href="<?php echo $article->permalink;?>" class="btn btn-es-default-o btn-sm"><?php echo JText::_('APP_ARTICLE_CONTINUE_READING');?></a>
		</div>
	</div>
	<div class="es-apps-item__ft es-bleed--bottom">
		<div class="o-grid">
			<div class="o-grid__cell">
				<div class="es-apps-item__meta">
					<div class="es-apps-item__meta-item">
						<ol class="g-list-inline g-list-inline--dashed">
							<li>
								<i class="fa fa-calendar"></i>&nbsp; <?php echo $this->html('string.date', $article->created, JText::_('DATE_FORMAT_LC3')); ?>
							</li>
							<li>
								<i class="fa fa-folder"></i>&nbsp; <a href="<?php echo $article->category->permalink;?>"><?php echo $article->category->title;?></a>
							</li>
						</ol>
					</div>
				</div>		
			</div>
		</div>
	</div>
</div>