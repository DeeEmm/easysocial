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
<div class="es-mobile-filter" data-es-group-members-filter data-es-slider>
	<div class="es-mobile-filter__hd">
		<div class="es-mobile-filter__hd-cell is-slider">
			<div class="es-mobile-filter-slider is-end-left" data-es-swiper-slider-group>
				<div class="es-mobile-filter-slider__content swiper-container" data-es-swiper-container>
					<div class="swiper-wrapper">

						<?php echo $this->html('mobile.filterGroup', 'APP_GROUP_MEMBERS_FILTER_ALL', 'all', ($active == 'all'), '', false, array('data-filter-item="all"'), $filterLinks->all); ?>

						<?php echo $this->html('mobile.filterGroup', 'APP_GROUP_MEMBERS_FILTER_MEMBERS', 'members', ($active == 'members'), '', false, array('data-filter-item="members"'), $filterLinks->members); ?>

						<?php echo $this->html('mobile.filterGroup', 'APP_GROUP_MEMBERS_FILTER_ADMINS', 'admin', ($active == 'admin'), '', false, array('data-filter-item="admin"'), $filterLinks->admin); ?>

						<?php if (($group->isClosed() || $group->isSemiOpen()) && ($group->isAdmin($this->my->id) || $group->isOwner($this->my->id))) { ?>
							<div class="es-mobile-filter-slider__item swiper-slide <?php echo $active == 'pending' ? 'is-active' : '';?>"
								data-es-swiper-item
								data-filter-item="pending"
							>
								<a href="<?php echo $filterLinks->pending; ?>" class="btn es-mobile-filter-slider__btn">
									<?php echo JText::_('APP_GROUP_MEMBERS_FILTER_PENDING');?>
								</a>
							</div>
						<?php } ?>

						<?php if ($group->isInviteOnly() && ($group->isAdmin($this->my->id) || $group->isOwner($this->my->id))) { ?>
							<div class="es-mobile-filter-slider__item swiper-slide <?php echo $active == 'invited' ? 'is-active' : '';?>"
								data-es-swiper-item
								data-filter-item="invited"
							>
								<a href="<?php echo $filterLinks->invited; ?>" class="btn es-mobile-filter-slider__btn">
									<?php echo JText::_('APP_GROUP_MEMBERS_FILTER_INVITED');?>
								</a>
							</div>
						<?php } ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
