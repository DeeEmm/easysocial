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
<?php if ($group->canSubsribeDigest()) { ?>
<div class="o-btn-group o-btn-group--subscribe">
	<button data-type="button" class="dropdown-toggle_ btn btn-es-default-o btn-sm" data-es-toggle="dropdown">
	<i class="far fa-envelope"></i>
	</button>

	<ul class="dropdown-menu dropdown-menu-right">
		<?php foreach ($intervals as $key => $interval) { ?>
		<?php
			$fa = 'far fa-envelope t-text--muted';
			if ($interval == $selected) {
				$fa = 'fa fa-check t-text--success';
			}
		?>
		<li class="<?php echo $interval == $selected ? 'active': ''; ?>"
			data-digest-subscribe
			data-id="<?php echo $group->id; ?>"
			data-type="group"
			data-interval="<?php echo $interval; ?>"
		>
			<a href="javascript:void(0);">
				<span class="o-flag">
					<span class="o-flag__image">
						<i class="<?php echo $fa; ?>"></i>
					</span>
					<span class="o-flag__body">
						<b><?php echo JText::_('COM_ES_DIGIST_' . strtoupper($key)); ?></b>
					</span>
				</span>
				<span class="t-sm-hidden"><?php echo JText::_('COM_ES_DIGIST_' . strtoupper($key) . '_DESC'); ?></span>
			</a>
		</li>
		<?php } ?>
	</ul>

</div>
<?php } ?>
