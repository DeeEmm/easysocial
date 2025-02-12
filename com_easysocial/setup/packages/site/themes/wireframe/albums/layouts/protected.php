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
<form class="o-box" method="POST" action="<?php echo JRoute::_('index.php');?>">
	<div class="">
		<div class="t-lg-mb--sm t-font-weight--bold">
			<i class="fas fa-lock t-text--muted"></i>
			<?php echo JText::_('COM_ES_PRIVATE_ALBUM'); ?>
		</div>
		<div class="t-lg-mb--md">
			<?php echo JText::_('COM_ES_ENTER_ALBUM_PASSWORD_DESCRIPTION'); ?>
		</div>
		<div class="form-inline">
			<div class="o-input-group">
				<input type="password" class="o-form-control" name="albumpassword_<?php echo $album->id; ?>" id="albumpassword_<?php echo $album->id; ?>"placeholder="<?php echo JText::_('COM_ES_ENTER_ALBUM_PASSWORD_PLACEHOLDER', true);?>">
				<span class="o-input-group__btn">
					<button class="btn btn-es-default">
						<?php echo JText::_('COM_ES_VIEW_PRIVATE_ALBUM');?>
					</button>
				</span>
			</div>

			<input type="hidden" name="option" value="com_easysocial" />
			<input type="hidden" name="controller" value="albums" />
			<input type="hidden" name="task" value="authorize" />
			<input type="hidden" name="id" value="<?php echo $album->id; ?>" />
			<input type="hidden" name="view" value="albums" />
			<?php echo $this->html('form.token'); ?>
		</div>
	</div>
</form>
