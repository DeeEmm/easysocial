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
<div class="es-side-widget__hd">
	<div class="es-side-widget__title">
		<?php echo $contents;?>

		<?php if ($action) { ?>
		<a href="<?php echo $action->link;?>" class="t-lg-pull-right btn btn-es-primary-o btn-xs" <?php echo $action->attributes;?>>
			<?php if ($action->text) { ?>
				<?php echo $action->text;?>
			<?php } ?>
			<?php if ($action->icon) { ?>
				<i class="fa <?php echo $action->icon;?>"></i>
			<?php } ?>

			<div class="o-loader o-loader--xs"></div>
		</a>
		<?php } ?>
	</div>
</div>