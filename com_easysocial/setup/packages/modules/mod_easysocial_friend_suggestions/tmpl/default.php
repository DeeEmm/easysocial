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
<div id="es" class="mod-es mod-es-friend-suggestions <?php echo $lib->getSuffix();?>">
	<?php echo ES::themes()->html('user.suggest', $limit, $refresh, false); ?>

	<?php if ($showMore) { ?>
	<div>
		<a href="<?php echo ESR::friends(array('filter' => 'suggest'));?>" class="btn btn-es-default-o btn-sm btn-block"><?php echo JText::_('MOD_EASYSOCIAL_FRIEND_SUGGESTIONS_VIEW_ALL'); ?></a>
	</div>
	<?php } ?>
</div>