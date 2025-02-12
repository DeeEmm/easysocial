<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div class="es-side-widget is-module">
	<?php echo $this->html('widget.title', 'COM_ES_DISCUSSIONS'); ?>

	<div class="es-side-widget__bd">
		<ul class="o-nav o-nav--stacked">
			<?php foreach ($discussions as $discussion) { ?>
			<li class="o-nav__item t-lg-mb--md">
				<a href="<?php echo $discussion->getPermalink();?>"><?php echo JText::_($discussion->title); ?></a>
				<div class="t-text--muted t-fs--sm">
					<?php echo ES::date($discussion->created)->format(JText::_('DATE_FORMAT_LC3')); ?>
				</div>
			</li>
			<?php } ?>
		</ul>
	</div>

	<div class="es-side-widget__ft">
		<?php echo $this->html('widget.viewAll', 'COM_ES_VIEW_ALL', $cluster->getAppPermalink('discussions')); ?>
	</div>
</div>
