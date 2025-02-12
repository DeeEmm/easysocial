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
<div class="es-list-item es-island" data-item data-id="<?php echo $cluster->id;?>" data-type="<?php echo $cluster->getType();?>">

	<div class="es-list-item__media">
		<?php echo $this->html('avatar.cluster', $cluster, 'default', true, false, '', true, $cluster->getEditPermalink()); ?>
	</div>

	<div class="es-list-item__context">
		<div class="es-list-item__hd">
			<div class="es-list-item__content">

				<div class="es-list-item__title">
					<?php echo $this->html('html.cluster', $cluster, false, 'top-left', $cluster->getEditPermalink()); ?>
				</div>

				<div class="es-list-item__meta">
					<ol class="g-list-inline g-list-inline--delimited">
						<li>
							<a href="<?php echo $cluster->getCategory()->getPermalink();?>">
								<i class="fa fa-folder"></i>&nbsp; <?php echo $cluster->getCategory()->getTitle();?>
							</a>
						</li>

						<li data-breadcrumb="&#183;">
							<?php echo $this->html($cluster->getType() . '.type', $cluster); ?>
						</li>

						<li data-breadcrumb="&#183;">
							<i class="fa fa-user-friends"></i>&nbsp; <?php echo $this->html('cluster.members', $cluster); ?>
						</li>
					</ol>
				</div>
			</div>

			<div class="es-list-item__action">
				<a href="javascript:void(0);" class="btn btn-es-danger-o" data-reject>
					<?php echo JText::_('COM_EASYSOCIAL_REJECT_BUTTON'); ?>
				</a>

				<a href="javascript:void(0);" class="btn btn-es-primary" data-approve>
					<?php echo JText::_('COM_EASYSOCIAL_APPROVE_BUTTON'); ?>
				</a>
			</div>
		</div>
	</div>
</div>
