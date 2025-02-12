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
<div class="es-list <?php echo !$clusters ? 'is-empty' : '';?>">
	<?php if ($clusters) { ?>
		<?php foreach ($clusters as $cluster) { ?>
		<div class="es-list__item">
			<a href="javascript:void(0);" class="es-list-item" data-story-selector data-uid="<?php echo $cluster->id;?>" data-type="<?php echo $clusterType;?>">
				<div class="es-list-item__media">
					<?php echo $this->html('avatar.cluster', $cluster, 'default', false, false, '', false); ?>
				</div>
				<div class="es-list-item__context">
					<div class="es-list-item__title">
						<?php echo $cluster->getTitle();?>
					</div>
				</div>
			</a>
		</div>
		<?php } ?>
	<?php } else { ?>
		<?php echo $this->html('html.emptyBlock', $emptyText, $emptyIcon); ?>
	<?php } ?>
</div>
