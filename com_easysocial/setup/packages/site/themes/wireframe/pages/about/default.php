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
<div class="es-profile" data-es-page data-id="<?php echo $page->id;?>">

	<?php echo $this->html('cover.page', $page, $layout); ?>

	<div class="es-container" data-es-container>

		<?php echo $this->html('html.sidebar'); ?>

		<?php if ($this->isMobile()) { ?>
			<?php echo $this->includeTemplate('site/pages/about/mobile'); ?>
		<?php } ?>

		<div class="es-content">

			<?php echo $this->render('module', 'es-pages-about-before-contents'); ?>

			<div class="es-profile-info">
				<?php if ($steps) { ?>
					<?php echo $this->output('site/fields/about/default', array('steps' => $steps, 'canEdit' => $page->isAdmin(), 'objectId' => $page->id, 'routerType' => 'pages', 'item' => $page)); ?>
				<?php } ?>
			</div>

			<?php echo $this->render('module', 'es-pages-about-after-contents'); ?>
		</div>
	</div>
</div>
