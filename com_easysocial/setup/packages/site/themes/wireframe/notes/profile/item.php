<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div class="es-apps-item es-island" data-notes-item data-id="<?php echo $note->id;?>">
	<div class="es-apps-item__hd">
		<a href="<?php echo $note->permalink;?>" class="es-apps-item__title"><?php echo $note->title;?></a>

		<?php if ($user->isViewer()) { ?>
		<div class="es-apps-item__action">
			<div class="o-btn-group">
				<button type="button" class="dropdown-toggle_ btn btn-es-default-o btn-xs" data-es-toggle="dropdown">
					<i class="fa fa-caret-down"></i>
				</button>

				<ul class="dropdown-menu dropdown-menu-right">
					<li>
						<a href="javascript:void(0);" data-edit><?php echo JText::_('APP_NOTES_EDIT_BUTTON');?></a>
					</li>
					<li>
						<a href="javascript:void(0);" data-delete><?php echo JText::_('APP_NOTES_DELETE_BUTTON');?></a>
					</li>
				</ul>
			</div>
		</div>
		<?php } ?>
	</div>

	<div class="es-apps-item__bd">
		<div class="es-apps-item__desc">
			<?php echo $this->html('string.truncate', $note->content, 300, '', false, false); ?>
		</div>

		<div class="es-apps-item__item-action">
			<a href="<?php echo $note->permalink;?>" class="btn btn-es-default-o btn-sm"><?php echo JText::_('COM_ES_VIEW_POST');?></a>
		</div>
	</div>

	<div class="es-apps-item__ft es-bleed--bottom">
		<div class="o-grid">
			<div class="o-grid__cell">
				<div class="es-apps-item__meta">
					<div class="es-apps-item__meta-item">
						<ol class="g-list-inline g-list-inline--dashed">
							<li>
								<time datetime="<?php echo $this->html('string.date' , $note->created); ?>">
									<i class="fa fa-calendar"></i>&nbsp; <?php echo $this->html('string.date', $note->created, JText::_('DATE_FORMAT_LC3')); ?>
								</time>
							</li>
						</ol>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
