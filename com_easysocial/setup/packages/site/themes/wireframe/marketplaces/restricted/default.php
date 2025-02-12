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
<div class="es-restricted es-marketplaces-restricted">
	<?php if ($node instanceof SocialMarketplace) { ?>
		<div class="o-box t-lg-mb--lg">
			<div class="t-font-weight--bold">
				<?php echo JText::_('COM_ES_ABOUT_THIS_LISTING'); ?>
			</div>
			<div>
				<?php echo $node->getDescription();?>
			</div>
		</div>
	<?php } else if ($node instanceof SocialUser) { ?>
		<?php echo $this->html('cover.user', $node, 'marketplaces'); ?>
	<?php } else { ?>
		<?php echo $this->html('cover.' . $node->type, $node, 'marketplaces'); ?>
	<?php } ?>

	<?php echo $this->html('html.restricted', $label, $text); ?>
</div>
