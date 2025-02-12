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
<div data-feeds>
	<div class="fd-cf t-lg-mb--lg">
		<a href="<?php echo $backLink;?>" class="t-lg-pull-left btn btn-es-default-o btn-sm">
			&larr; <?php echo JText::_('COM_ES_OTHER_FEEDS'); ?>
		</a>
	</div>

	<div class="o-box t-lg-mb--lg" data-item data-id="<?php echo $feed->id;?>">
		<div class="o-grid">
			<div class="o-grid__cell">
				<a href="<?php echo $feed->getPermalink();?>" class="t-font-weight--bold">
					<i class="fa fa-rss-square"></i>&nbsp; <?php echo $feed->title;?>
				</a>
			</div>

			<div class="o-grid__cell o-grid__cell--auto-size">
				<ol class="g-list--horizontal">
					<li class="g-list__item t-lg-mr--md t-text--muted">
						<i class="far fa-clock"></i> <?php echo ES::date($feed->created)->toLapsed();?>
					</li>
				</ol>
			</div>
		</div>

		<?php if ($feed->items) { ?>
		<div class="o-box--border">
			<?php foreach ($feed->items as $item) { ?>
			<div class="es-rss-item">
				<a href="<?php echo @$item->get_link();?>" class="t-font-weight--bold" target="_blank"><?php echo @$item->get_title();?></a>

				<div>
					<?php echo $this->html('string.truncate', @$item->get_content(), 350); ?>
				</div>

				<div class="t-text--muted">
					<span class="t-fs--sm"><?php echo @$item->get_date(JText::_('COM_EASYSOCIAL_DATE_DMY')); ?></span>
				</div>
				<hr class="es-hr"/>
			</div>
			<?php } ?>
		</div>
		<?php } else { ?>
		<div>
			<?php echo JText::_('APP_FEEDS_EMPTY_FEED_RESULT'); ?>
		</div>
		<?php } ?>
	</div>
</div>
