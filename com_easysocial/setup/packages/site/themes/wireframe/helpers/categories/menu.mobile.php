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
<div class="es-mobile-filter__group" data-es-swiper-group data-type="categories">
	<div class="dl-menu-wrapper t-hidden" data-categories-menu>
		<div class="es-list">
		<?php if ($categories) { ?>
			<?php foreach ($categories as $category) { ?>
			<div class="es-list__item" data-filter-item data-type="category" data-id="<?php echo $category->id;?>">
				<div class="es-list-item es-island">
					<?php if ($hasAvatar) { ?>
					<div class="es-list-item__media">
						<?php echo $this->html('avatar.clusterCategory', $category); ?>
					</div>
					<?php } ?>
					<div class="es-list-item__context">
						<div class="es-list-item__hd">
							<div class="es-list-item__content">
								<div class="es-list-item__title">
									<a href="<?php echo $category->getFilterPermalink();?>" title="<?php echo $this->html('string.escape' , $category->get('title'));?>">
										<?php echo $category->getTitle();?>
									</a>
									<?php if ($displayCounters) { ?>
									<span class="t-text--muted t-font-weight--normal" data-counter="<?php echo $category->total;?>">
										(<?php echo $category->total;?>)
									</span>
									<?php } ?>
								</div>
								<div class="es-list-item__meta">
									<?php echo $this->html('string.truncate', $category->getDescription(), 80, '', false, false, false, true);?>
								</div>

								<?php if (!empty($category->childs)) { ?>
								<div>
									<ol class="g-list-inline g-list-inline--delimited" data-behavior="sample_code">
										<?php foreach ($category->childs as $child) { ?>
										<li data-breadcrumb="|">
											<a href="<?php echo $child->getFilterPermalink(); ?>"><?php echo $child->getTitle(); ?></a>
											<?php if ($displayCounters) { ?>
												<span class="t-text--muted t-font-weight--normal" data-counter="<?php echo $child->total;?>">(<?php echo $child->total;?>)</span>
											<?php } ?>
										</li>
										<?php } ?>
									</ol>
								</div>
								<?php } ?>
							</div>
						</div>
					</div>
				</div>
			</div>
			<?php } ?>
		<?php } ?>
		</div>
	</div>
</div>
