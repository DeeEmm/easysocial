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
<div class="es-media-nav">
	<div class="es-media-nav__prev">
		<?php if ($navigation->prev) { ?>
		<a href="<?php echo $navigation->prev->getPermalink(true, $uid, $type);?>" >
			<div class="es-media-nav__img">
				<img src="<?php echo $prevImage;?>">
			</div>
			<div class="es-media-nav__content">
				<div class="es-media-nav__meta">
					<?php echo JText::_('COM_ES_NAVIGATION_PREVIOUS'); ?>
				</div>
				<div class="es-media-nav__title">
					<?php echo $navigation->prev->getTitle();?>
				</div>
			</div>
		</a>
		<?php } ?>
	</div>

	<div class="es-media-nav__next">
		<?php if ($navigation->next) { ?>
		<a href="<?php echo $navigation->next->getPermalink(true, $uid, $type);?>">
			<div class="es-media-nav__content">
				<div class="es-media-nav__meta">
					<?php echo JText::_('COM_ES_NAVIGATION_NEXT'); ?>
				</div>
				<div class="es-media-nav__title">
					<?php echo $navigation->next->getTitle();?>
				</div>
			</div>
			<div class="es-media-nav__img">
				<img src="<?php echo $nextImage;?>">
			</div>
		</a>
		<?php } ?>
	</div>
</div>
