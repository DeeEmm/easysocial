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

			<div class="es-stage__curtain es-stage__curtain--off ">

				<h3 class="es-stage__title">
					<?php echo JText::_( 'COM_EASYSOCIAL_HEADING_BADGES' );?>
				</h3>
				<div class="es-stage__desc">
					<?php echo JText::_( 'COM_EASYSOCIAL_HEADING_BADGES_DESC' ); ?>
				</div>
				
			</div>

			<div class="es-stage__audience">
				
				<div class="es-stage__audience-result">
					<?php if( $badges ){ ?>
					<div class="es-points-list">
					<?php foreach( $badges as $badge ){ ?>
						<div class="es-points-list__item">
							<div class="o-box t-lg-pt--md">
								<div>
									
									<a class="t-block t-lg-mb--lg" href="<?php echo $badge->getPermalink();?>">
										<img class="es-badges-icon" alt="<?php echo $this->html( 'string.escape' , $badge->get( 'title' ) );?>" src="<?php echo $badge->getAvatar();?>" />
									</a>
									<h5 class="es-points-list__title">
										<a href="<?php echo $badge->getPermalink();?>">
											<?php echo $badge->get( 'title' ); ?>
										</a>
									</h5>

									<div class="t-text--muted t-lg-mb--lg">
										<?php echo $badge->get( 'description' ); ?>
									</div>
									
								</div>
								<div class="o-box--border">
									<div><?php echo JText::_('COM_EASYSOCIAL_BADGES_ACHIEVERS');?>: <span><?php echo $badge->getTotalAchievers();?></span>
									</div>
								</div>
							</div>
						</div>
						
					<?php } ?>
					</div>

					<?php } else { ?>
					<div class="o-empty">
						<div class="o-empty">
							<div class="o-empty__content">
								<div class="o-empty__text"><?php echo JText::_( 'COM_EASYSOCIAL_BADGES_NO_BADGES_YET' ); ?></div>
							</div>
						</div>
					</div>
					<?php } ?>

				</div>

			</div>
		</div>
		
		<?php echo $pagination->getListFooter( 'site' );?>
	</div>
</div>