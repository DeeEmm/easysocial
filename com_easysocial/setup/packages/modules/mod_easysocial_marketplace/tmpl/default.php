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
<div id="es" class="mod-es mod-es-marketplaces <?php echo $lib->getSuffix();?>">
	<div class="es-cards es-cards--1">
		<?php foreach ($listings as $listing) { ?>
			<div class="es-cards__item">
				<div class="es-card">
					<div class="es-card__hd">
						<a class="embed-responsive embed-responsive-16by9" href="<?php echo $listing->getPermalink();?>">
							<div class="embed-responsive-item es-card__cover" style="background-image : url(<?php echo $listing->getSinglePhoto()?>);background-position: center center;">
							</div>
						</a>
					</div>

					<div class="es-card__bd es-card--border">

						<a class="es-card__title" href="<?php echo $listing->getPermalink();?>"><?php echo $listing->getTitle();?></a>

						<div class="es-card__meta t-lg-mb--sm">
							<ol class="g-list-inline g-list-inline--delimited">
								<li>
									<?php echo $listing->getPriceTag();?>
								</li>
							</ol>
						</div>
					</div>
				</div>
			</div>
		<?php } ?>
	</div>

	<?php if ($params->get('display_alllink', true)) { ?>
	<div>
		<a href="<?php echo ESR::marketplaces();?>" class="btn btn-es-default-o btn-sm btn-block"><?php echo JText::_('MOD_EASYSOCIAL_MARKETPLACES_ALL_LISTINGS'); ?></a>
	</div>
	<?php } ?>
</div>
