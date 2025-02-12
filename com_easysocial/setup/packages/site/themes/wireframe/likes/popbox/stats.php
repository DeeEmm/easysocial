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
<div data-es-reaction-stats>
	<div class="es-reaction-stats-pop">

		<div class="es-reaction-stats-pop__hd">
			<div class="es-reaction-icon-stat">
				<?php $i = 0; ?>
				<?php foreach ($reactions as $reaction) { ?>
				<a href="#<?php echo $reaction->getKey();?>" class="es-reaction-icon-stat__item <?php echo $reactionFilter == $reaction->getKey() ? 'is-active' : '';?>" data-es-toggle="tab">
					<div class="es-reaction-icon-stat__avatar">
						<div class="es-icon-reaction es-icon-reaction--sm es-icon-reaction--<?php echo $reaction->getKey();?>"></div>
					</div>
					<div class="es-reaction-icon-stat__counter"><?php echo $reaction->getTotal();?></div>
				</a>
				<?php $i++;?>
				<?php } ?>
			</div>
		</div>

		<div class="es-reaction-stats-pop__bd">
			<div class="tab-content">
				<?php $i = 0; ?>
				<?php foreach ($reactions as $reaction) { ?>
				<div class="tab-pane <?php echo $reactionFilter == $reaction->getKey() ? 'active' : '';?>" id="<?php echo $reaction->getKey();?>">
						<div class="es-reaction-stats-list">
							<?php foreach ($reaction->users as $user) { ?>
							<div class="es-reaction-stats-list__item">
								<div class="o-media">
									<div class="o-media__image">
										<?php echo $this->html('avatar.' . $user->getType(), $user, 'sm', false); ?>
									</div>
									<div class="o-media__body">
										<?php echo $this->html('html.' . $user->getType(), $user, false); ?>
									</div>
								</div>
							</div>
							<?php } ?>
						</div>

					<?php $i++;?>
				</div>
				<?php } ?>
			</div>
		</div>
	</div>
</div>
