<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div class="es-mobile-filter__hd-cell is-last">
	<div class="es-mobile-filter-toggle">
		<div class="dropdown_">
			<a href="javascript:void(0);" class="btn-control btn btn-es-default" data-es-toggle="dropdown">
				<i class="<?php echo $icon;?>"></i>
			</a>

			<ul class="dropdown-menu dropdown-menu-right">
				<?php foreach ($actions as $action) { ?>
					<?php if ($action) { ?>
						<?php echo $action; ?>
					<?php } ?>
				<?php } ?>
			</ul>
		</div>
	</div>
</div>
