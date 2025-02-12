<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div class="es-list__item">
	<div class="es-list-item es-island" data-item data-id="<?php echo $photo->id;?>">
		<div class="es-list-item__media">
			<?php echo $this->html('avatar.mini', $photo->_('title'), $photo->getPermalink(), $photo->getSource()); ?>
		</div>
		<div class="es-list-item__context">
			<div class="es-list-item__hd">
				<div class="es-list-item__content">

					<div class="es-list-item__title">
						<a href="<?php echo $photo->getPermalink();?>" class="">
						<?php echo $photo->_('title'); ?>
						</a>
					</div>

					<div class="es-list-item__meta">
						<ol class="g-list-inline g-list-inline--delimited">
							<?php if ($displayType) { ?>
							<li data-breadcrumb="&#183;">
								<i class="fa fa-image"></i>&nbsp; <?php echo JText::_('COM_ES_PHOTOS');?>
							</li>
							<?php } ?>
							<li>
						</ol>
					</div>
				</div>

			</div>

			<div class="es-list-item__bd">
				<div class="es-list-item__desc">
					<?php echo JText::sprintf('COM_EASYSOCIAL_SEARCH_RESULT_PHOTOS_UPLOADED_ON' , $this->html('string.date' , $photo->finder->publish_start_date , 'COM_EASYSOCIAL_PHOTOS_DATE_FORMAT')); ?>
				</div>
			</div>

		</div>
	</div>
</div>