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
<div id="es" class="mod-es mod-es-sidebar-articles <?php echo $moduleLib->getSuffix();?>">
	<div class="es-sidebar">
		<?php if ($showCreateArticleButton) { ?>
			<a href="<?php echo JRoute::_('index.php?option=com_content&view=form&layout=edit');?>" class="btn btn-es-primary btn-block t-lg-mb--xl"><?php echo JText::_('COM_ES_NEW_ARTICLE'); ?></a>
		<?php } ?>

		<div class="es-side-widget">
			<?php echo $this->html('widget.title', 'COM_ES_STATISTICS'); ?>

			<div class="es-side-widget__bd">
				<ul class="o-nav o-nav--stacked">
					<li class="o-nav__item t-lg-mb--sm">
						<span class="o-nav__link t-text--muted">
							<i class="es-side-widget__icon fa fa-sticky-note-o t-lg-mr--md"></i>
							<b><?php echo $total;?></b> <?php echo JText::_('COM_ES_ARTICLES');?>
						</span>
					</li>
				</ul>
			</div>
		</div>
	</div>
</div>
