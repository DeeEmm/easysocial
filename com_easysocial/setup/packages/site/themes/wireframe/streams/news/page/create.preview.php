<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2016 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div class="row mb-10 mt-10">
	<div class="col-md-12">
		<h4><a href="<?php echo $permalink;?>"><?php echo $news->title; ?></a></h4>

		<?php if ($appParams->get('stream_display_date', true)) { ?>
		<div>
			<i class="fa fa-calendar"></i>&nbsp; <?php echo JText::sprintf('APP_PAGE_NEWS_STREAM_META_CREATED_ON', ES::date($news->created)->format(JText::_('DATE_FORMAT_LC'))); ?>
		</div>
		<?php } ?>

		<div class="mb-10 mt-10 news-snippet">
			<?php echo $news->content; ?>
		</div>

		<div>
			<a href="<?php echo $permalink;?>" class="mt-5"><?php echo JText::_('APP_PAGE_NEWS_STREAM_CONTINUE_READING'); ?> &rarr;</a>
		</div>
	</div>
</div>
