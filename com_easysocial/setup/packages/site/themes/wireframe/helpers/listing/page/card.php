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
<div class="es-cards__item">
	<div class="es-card <?php echo $page->isFeatured() ? ' is-featured' : '';?>">
		<div class="es-card__hd">
			<div class="es-card__action-group">
				<?php if ($page->canAccessActionMenu()) { ?>
					<div class="es-card__admin-action">
						<div class="pull-right dropdown_">
							<a href="javascript:void(0);" class="btn btn-es-default-o btn-sm dropdown-toggle_" data-es-toggle="dropdown">
								<i class="fa fa-ellipsis-h"></i>
							</a>

							<ul class="dropdown-menu">
								<?php echo $this->html('page.adminActions', $page); ?>

								<?php $reportHtml = $this->html('page.report', $page); ?>

								<?php if ($reportHtml) { ?>
								<li>
									<?php echo $reportHtml; ?>
								</li>
								<?php } ?>
							</ul>
						</div>
					</div>
				<?php } ?>
			</div>

			<?php echo $this->html('card.cover', $page); ?>
		</div>

		<div class="es-card__bd es-card--border has-avatar">
			<?php echo $this->html('card.avatar', $page); ?>

			<?php echo $this->html('card.icon', 'featured', 'COM_EASYSOCIAL_PAGES_FEATURED_PAGES'); ?>

			<?php echo $this->html('card.title', $page); ?>

			<div class="es-card__meta t-lg-mb--sm">
				<ol class="g-list-inline g-list-inline--delimited">
					<li>
						<i class="fa fa-folder"></i>&nbsp; <a href="<?php echo $page->getCategory()->getFilterPermalink();?>"><?php echo $page->getCategory()->getTitle();?></a>
					</li>
					<li>
						<?php echo $this->html('page.type', $page); ?>
					</li>
				</ol>
			</div>

			<?php if ($this->config->get('pages.layout.listingsdesc')) { ?>
			<div class="es-card__desc">
				<?php if ($page->description) { ?>
					<?php echo $this->html('string.truncate', $page->getDescription(), 200, '', false, false, false, true);?>
				<?php } else { ?>
					<?php echo JText::_('COM_EASYSOCIAL_PAGES_NO_DESCRIPTION_YET'); ?>
				<?php }?>
			</div>
			<?php } ?>
		</div>

		<div class="es-card__ft es-card--border">
			<div class="es-card__meta">
				<ol class="g-list-inline g-list-inline--delimited">
					<li data-es-provide="tooltip" data-original-title="<?php echo JText::sprintf(ES::string()->computeNoun('COM_EASYSOCIAL_PAGES_LIKERS', $page->getTotalMembers()), $page->getTotalMembers()); ?>">
						<i class="far fa-thumbs-up"></i>&nbsp; <span data-page-like-count-<?php echo $page->id; ?> ><?php echo $page->getTotalMembers();?></span>
					</li>

					<li class="pull-right">
						<?php echo $this->html('page.action', $page); ?>
					</li>
				</ol>
			</div>
	   </div>
	</div>
</div>
