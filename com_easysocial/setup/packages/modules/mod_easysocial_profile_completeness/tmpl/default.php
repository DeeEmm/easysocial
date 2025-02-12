<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2014 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div id="es" class="mod-es-profile-completeness <?php echo $lib->getSuffix();?>">
	
	<div class="mod-es-title"><?php echo JText::sprintf('MOD_EASYSOCIAL_PROFILE_COMPLETENESS_PERCENTAGE', $percentage); ?></div>

	<div class="progress ">
		<div class="progress-bar progress-bar-success" role="progressbar" style="width: <?php echo $percentage; ?>%;"></div>
	</div>
	<?php if ($percentage < 100) { ?>
		<a href="<?php echo ESR::profile(array('layout' => 'edit')); ?>" class="t-fs--sm"><?php echo JText::_('MOD_EASYSOCIAL_PROFILE_COMPLETENESS_COMPLETE_PROFILE_NOW'); ?></a>
	<?php } ?>
</div>
