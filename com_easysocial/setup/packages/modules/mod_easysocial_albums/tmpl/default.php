<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2016 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div id="es" class="mod-es mod-es-albums <?php echo $lib->getSuffix();?>">
	<?php if ($recentAlbums) { ?>
		<?php echo $lib->html('widget.albums', $recentAlbums);?>

		<div class="mod-es-action">
			<a href="<?php echo ESR::albums(); ?>" class="btn btn-es-default-o btn-sm btn-block"><?php echo JText::_('COM_EASYSOCIAL_ALBUMS_VIEW_ALL_ALBUMS'); ?></a>
		</div>
	<?php } else { ?>
	<div class="t-text--muted">
		<?php echo JText::_('MOD_EASYSOCIAL_ALBUMS_EMPTY'); ?>
	</div>
	<?php } ?>
</div>