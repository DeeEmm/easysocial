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
<div id="es" class="mod-es mod-es-ads-showcase">
	<div id="es-ads-showcase" class="es-ads-showcase carousel slide mootools-noconflict" data-es-ads-showcase data-interval="<?php echo $rotateInterval;?>">
		<ol class="es-ads-showcase__indicators carousel-indicators">
			<?php for ($i = 0; $i < count($ads); $i++) { ?>
				<li data-target=".es-ads-showcase" data-bp-slide-to="<?php echo $i;?>" class="<?php echo $i == 0 ? 'active' : '';?>"></li>
			<?php } ?>
		</ol>
		<div class="carousel-inner">
			<?php $i = 0; ?>
			<?php foreach ($ads as $ad) { ?>
				<?php ++$i;?>
				<div class="item<?php echo $i == 1 ? ' active' : '';?>" data-id="<?php echo $ad->id; ?>" data-link="<?php echo $ad->getLink(); ?>" data-module-ads-item>
					<div class="es-stream-embed is-ads">
						<a href="javascript:void(0);" class="es-stream-embed__cover" data-module-ads-link="">
							<div class="es-stream-embed__cover-img" style="background-image: url('<?php echo $ad->getCover(); ?>');"></div>
						</a>
						<div class="o-grid o-grid--center es-stream-embed--border">
							<div class="o-grid__cell">
								<a href="javascript:void(0);" class="es-stream-embed__title es-stream-embed--border" data-module-ads-link="">
									<?php echo $ad->title; ?>
								</a>

								<?php if ($ad->link) { ?>
									<div class="es-stream-embed__meta">
										<?php echo $ad->getLink(false); ?>
									</div>
								<?php } ?>
								<div class="es-stream-embed__desc t-text--muted">
									<?php echo $ad->content; ?>
								</div>
							</div>
						</div>
						<?php if ($ad->hasButton() || count($ads) > 1) { ?>
						<div class="es-stream-embed__action">
							<?php if ($ad->hasButton()) { ?>
								<a href="javascript:void(0);" class="btn btn-es-default-o btn-sm" data-module-ads-link=""><?php echo $ad->getButtonText(); ?></a>
							<?php } ?>
							
							<?php if (count($ads) > 1 ) { ?>
								<div class="es-ads-showcase__control o-btn-group">
									<a class="btn btn-es-default-o btn-sm" href="javascript:void(0);" role="button" data-bp-slide="prev">
										<span class="fa fa-angle-left"></span>
									</a>
									<a class="btn btn-es-default-o btn-sm" href="javascript:void(0);" role="button" data-bp-slide="next">
										<span class="fa fa-angle-right"></span>
									</a>
								</div>
							<?php } ?>
						</div>
						<?php } ?>
					</div>
				</div>
			<?php } ?>
		</div>
	</div>
</div>
