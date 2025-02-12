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
<div>
	<div class="es-cat-header">
		<div class="es-cat-header__hd">
			<div class="o-flag">
				<div class="o-flag__image o-flag--top">
					<?php if ($avatar) { ?>
						<?php echo $this->html('avatar.mini', $title, $permalink, $avatar, 'default', 'es-cat-header__avatar', '', true, 'square'); ?>
					<?php } ?>
				</div>

				<div class="o-flag__body">
					<div class="es-cat-header__hd-content-wrap">
						<div class="es-cat-header__hd-content">
							<a href="<?php echo $permalink;?>" class="es-cat-header__title-link"><?php echo $this->html('string.escape', $title);?></a>
							<div class="es-cat-header__desc">
								<?php echo $this->html('string.truncate', $description, 200, '', false, true);?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<?php if ($moreText) { ?>
		<div class="es-cat-header__ft t-text--right">
			<a class="btn btn-es-default-o btn-sm" href="<?php echo $permalink;?>"><?php echo JText::_($moreText);?></a>
		</div>
		<?php } ?>
	</div>
</div>
