<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2020 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div class="es-list__item">
	<div class="es-list-item es-island <?php echo $page->isFeatured() ? 'is-featured' : '';?>" data-item data-id="<?php echo $page->id;?>">
		<div class="es-list-item__media">
			<?php echo $this->html('avatar.cluster', $page); ?>
		</div>

		<div class="es-list-item__context">
			<div class="es-list-item__hd">
				<div class="es-list-item__content">
					<div class="es-list-item__title">
						<?php echo $this->html('html.cluster', $page); ?>
					</div>

					<div class="es-list-item__meta">
						<ol class="g-list-inline g-list-inline--delimited t-text--muted">
							<?php if ($displayType) { ?>
							<li>
								<i class="fa fa-briefcase"></i>&nbsp; <?php echo JText::_('COM_ES_PAGES');?>
							</li>
							<?php } ?>

							<li data-breadcrumb="&#183;">
								<?php echo $this->html('page.type', $page, 'bottom', false, false); ?>
							</li>

							<li data-breadcrumb="&#183;">
								<a href="<?php echo $page->getCategory()->getPermalink();?>"><?php echo $page->getCategory()->getTitle();?></a>
							</li>

							<li data-breadcrumb="&#183;">
								<?php echo JText::sprintf(ES::string()->computeNoun('COM_EASYSOCIAL_PAGES_LIKERS', $page->getTotalMembers()), $page->getTotalMembers() ); ?>
							</li>
						</ol>
					</div>
				</div>

				<div class="es-list-item__state">
					<div class="es-label-state es-label-state--featured" data-original-title="<?php echo JText::_('COM_EASYSOCIAL_PAGES_FEATURED_PAGES');?>" data-es-provide="tooltip">
						<i class="es-label-state__icon"></i>
					</div>
				</div>

				<div class="es-list-item__action">
					<div class="o-btn-group">
						<?php echo $this->html('page.action', $page); ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
