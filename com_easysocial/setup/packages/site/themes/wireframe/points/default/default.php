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
<div class="es-container">
	<div class="es-content">

		<div class="es-stage es-island" data-dashboard>

			<div class="es-stage__curtain es-stage__curtain--off es-bleed--top">

				<h3 class="es-stage__title" data-heading-title>
					 <?php echo JText::_('COM_EASYSOCIAL_POINTS_HEADING');?>
				</h3>
				<div class="es-stage__desc" data-heading-desc>
					 <?php echo JText::_('COM_EASYSOCIAL_POINTS_HEADING_DESC'); ?>
				</div>

			</div>

			<div class="es-stage__audience">

				<div class="es-stage__audience-result" data-points-wrapper>
					<div class="es-points-list">

						<?php foreach ($points as $point) { ?>
						<div class="es-points-list__item">
							<div class="o-box t-lg-pt--md">
								<div>
									<div class="es-point-badge t-lg-mb--lg <?php echo $point->points < 0 ? ' es-point-badge--alert' : '';?><?php echo $point->points > 10 ? ' es-point-badge--success' : '';?>">
										<?php echo $point->points;?>
									</div>
									<h5 class="es-points-list__title">
										<a href="<?php echo $point->getPermalink();?>">
											<?php echo JText::_( $point->title );?>
										</a>
									</h5>

									<div class="t-text--muted t-lg-mb--lg">
										<?php echo $point->get( 'description' ); ?>
									</div>
									
								</div>
								<div class="o-box--border">
									<div>
										<?php echo JText::_('COM_EASYSOCIAL_POINTS_ACHIEVERS');?>: 
										<span data-id="<?php echo $point->id; ?>" data-points-counter>
											<div class="o-loader o-loader--sm o-loader--inline is-active"></div>
										</span>
									</div>
								</div>
							</div>
						</div>
						<?php } ?>

					</div>
				</div>

			</div>
		</div>
		
		<?php echo $pagination->getListFooter( 'site' ); ?>
		
	</div>
</div>