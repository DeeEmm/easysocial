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

<div class="es-container app-followers app-pages" data-es-page-followers data-es-container data-id="<?php echo $page->id;?>" data-return="<?php echo $returnUrl;?>">

	<?php echo $this->html('html.sidebar'); ?>

	<?php if ($this->isMobile()) { ?>
		<?php echo $this->output('apps/page/followers/pages/mobile.filters'); ?>
	<?php } ?>

	<div class="es-content">
		<?php echo $this->render('module', 'es-page-followers-before-contents'); ?>

		<div class="o-input-group o-input-group">
			<input type="text" class="o-form-control" data-search-input placeholder="<?php echo JText::_('COM_ES_SEARCH_PLACEHOLDER'); ?>" />
		</div>

		<div class="t-lg-mt--xl" data-contents>
			<?php echo $this->html('listing.loader', 'listing', 2, 1); ?>

			<?php echo $this->includeTemplate('apps/page/followers/pages/wrapper'); ?>
		</div>

		<?php echo $this->render('module', 'es-page-followers-after-contents'); ?>
	</div>
</div>
