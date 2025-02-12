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
<?php for ($i = 1; ($i - 1 < $limit) && ($i - 1 < $total); $i++) { ?>
<?php $user = $users[$i - 1]; ?>
<?php if ($linkUsers) { ?><a href="<?php echo $user->getPermalink(); ?>"><?php } ?><?php echo $boldNames ? '<b>' : '';?><?php echo $user->getName(); ?><?php echo $boldNames ? '</b>' : '';?><?php if ($linkUsers) { ?></a><?php } ?>
<?php if ($i < $limit && $i + 1 < $total) { ?><?php echo JText::_('COM_EASYSOCIAL_COMMA') . ' ';?><?php } ?>
<?php if ($i < $limit && $i + 1 == $total) { ?><?php echo ' ' . JText::_('COM_EASYSOCIAL_AND') . ' '; ?><?php } ?>
<?php if( $total > $limit && ( $i == $limit ) ){
	$remaining = array_slice( $users, $i );
	$idStr = '';
	if ($remaining) {
		foreach ($remaining as $item) {
			$idStr .= $idStr ? '|' . $item->id : $item->id;
		}
	}
?>
<?php echo ' ' . JText::_('COM_EASYSOCIAL_AND') . ' '; ?>
<?php if ($showPopbox) { ?>
	<script>EasySocial.require().script('site/users/popbox').done();</script>
	<a href="javascript:void(0);" rel="nofollow" data-popbox="module://easysocial/users/popbox" data-popbox-tooltip="users" data-popbox-position="top-left" data-ids="<?php echo $idStr; ?>">
<?php } ?>
<?php echo $boldNames ? '<b>' : '';?><?php echo JText::sprintf('COM_EASYSOCIAL_AND_OTHERS', ($total - $limit)); ?><?php echo $boldNames ? '</b>' : '';?>
	<?php if ($showPopbox) { ?></a><?php } ?>
<?php } ?>
<?php } ?>
