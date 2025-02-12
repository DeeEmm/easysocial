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
<a href="<?php echo $permalink;?>" title="<?php echo $this->html('string.escape', $cluster->getName());?>"
	<?php if ($popbox) { ?>
	data-popbox="module://easysocial/cluster/popbox"
	data-popbox-position="<?php echo $popboxPosition;?>"
	data-<?php echo $cluster->getType();?>-id="<?php echo $cluster->id;?>"
	data-id="<?php echo $cluster->id;?>"
	data-type="<?php echo $cluster->getTypePlural();?>"
	<?php } ?>
>
	<?php echo $cluster->getName();?>
</a>

<?php if ($cluster->isVerified()) { ?><i class="es-verified" data-es-provide="tooltip" data-original-title="<?php echo JText::_('COM_ES_VERIFIED_' . strtoupper($cluster->getType()));?>"></i><?php } ?>
