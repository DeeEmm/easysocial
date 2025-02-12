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
<div class="popbox-header">
	<div class="es-title"><?php echo JText::_('COM_EASYSOCIAL_REPOST_VIEW_ALL_AUTHORS_TITLE'); ?></div>

	<a href="javascript:void(0);" data-popbox-close class="popbox-header__mobile-close"><i class="fa fa-times"></i></a>
</div>

<div class="popbox-body">
	<ul class="g-list-inline t-lg-p--md">
		<?php foreach ($users as $user) { ?>
		<li class="t-lg-mb--md t-lg-mr--lg">
			<?php echo $this->html('avatar.' . $user->getType(), $user, 'default', false, true); ?>
		</li>
		<?php } ?>
	</ul>
</div>
