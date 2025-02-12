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

<?php if (!$browseView) { ?>
	<?php echo $this->html('cover.user', $user, 'pages'); ?>
<?php } ?>

<div class="es-container es-pages" data-es-pages data-es-container>

	<?php echo $this->html('html.sidebar'); ?>

	<?php if ($this->isMobile()) { ?>
		<?php echo $this->includeTemplate('site/pages/default/mobile.filters'); ?>
	<?php } ?>

	<div class="es-content">
		<?php echo $this->render('module' , 'es-pages-before-contents'); ?>

		<div class="es-page-listing" data-wrapper>
			<?php echo $this->includeTemplate('site/pages/default/items'); ?>
		</div>

		<?php echo $this->render('module', 'es-pages-after-contents'); ?>
	</div>
</div>
