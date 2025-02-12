<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2016 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div class="es-side-widget">
	<div class="es-side-widget__hd">
		<div class="es-side-widget__title"><?php echo JText::_('APP_PAGE_PAGES_FRIENDS_IN_PAGE_WIDGET_TITLE');?>
			<span class="es-side-widget__title-counter">(<?php echo $total;?>)</span>	
		</div>
		
	</div>

	<div class="es-side-widget__bd">
		<?php if ($friends) { ?>
		<ul class="g-list-inline">
			<?php foreach ($friends as $friend) { ?>
			<li class="t-lg-mb--md t-lg-mr--lg">
				<div class="o-avatar-wrap">
					<?php echo $this->html('avatar.user', $friend, 'default', true, false);?>
				</div>
			</li>
			<?php } ?>
		</ul>
		<?php } else { ?>
		<div class="t-fs--sm">
			<?php echo JText::_('APP_PAGE_PAGES_NO_FRIENDS'); ?>
		</div>
		<?php } ?>
	</div>
</div>
