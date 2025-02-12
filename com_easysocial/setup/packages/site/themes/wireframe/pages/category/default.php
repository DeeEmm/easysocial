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
<div class="es-page-category">

	<?php echo $this->html('header.pagecategory', $category); ?>

	<div class="es-container t-lg-mt--xl" data-es-pages-category>

		<!-- do not remove this element. This element is needed for the stream loodmore to work properly -->
		<div data-filter-item data-type="pagecategory" data-id="<?php echo $category->id;?>" class="active"></div>

		<?php echo $this->html('html.sidebar'); ?>

		<?php if ($this->isMobile()) { ?>
			<?php echo $this->includeTemplate('site/pages/category/mobile'); ?>
		<?php } ?>

		<div class="es-content">
			<?php echo $this->html('html.snackbar', 'COM_EASYSOCIAL_PAGES_RECENT_UPDATES', 'div'); ?>

			<div class="es-content-wrap" data-es-page-item-content>
				<?php echo $stream->html();?>
			</div>
		</div>
	</div>
</div>
