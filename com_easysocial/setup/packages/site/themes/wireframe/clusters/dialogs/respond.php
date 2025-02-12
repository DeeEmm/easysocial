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
<dialog>
	<width>450</width>
	<height>150</height>
	<selectors type="json">
	{
		"{closeButton}": "[data-close-button]",
		"{rejectButton}": "[data-reject-button]",
		"{acceptButton}": "[data-accept-button]",
		"{respondForm}": "[data-cluster-invite-respond]",
		"{responseValue}": "[data-respond-value]"
	}
	</selectors>
	<bindings type="javascript">
	{
		"{closeButton} click": function() {
			this.parent.close();
		}
	}
	</bindings>
	<title><?php echo JText::sprintf('COM_EASYSOCIAL_' . strtoupper($cluster->getTypePlural()) . '_DIALOG_RESPOND_TITLE', $cluster->getName()); ?></title>
	<content>
		<p class="mt-5">
			<img src="<?php echo $cluster->getAvatar();?>" class="ml-5 es-avatar" align="right" />

			<?php echo JText::sprintf('COM_EASYSOCIAL_' . strtoupper($cluster->getTypePlural()) . '_DIALOG_RESPOND_CONTENT', $inviter->getName() , $cluster->getName());?>
		</p>

		<form data-cluster-invite-respond method="post" action="<?php echo JRoute::_('index.php');?>">
			<?php echo $this->html('form.token'); ?>
			<input type="hidden" name="option" value="com_easysocial" />
			<input type="hidden" name="controller" value="<?php echo $cluster->getTypePlural(); ?>" />
			<input type="hidden" name="task" value="respondInvitation" />
			<input type="hidden" name="id" value="<?php echo $cluster->id;?>" />
			<input type="hidden" name="inviterId" value=<?php echo $inviter->id; ?> />
			<input type="hidden" name="action" value="" data-respond-value />
		</form>
	</content>
	<buttons>
		<button type="button" class="btn btn-es-danger btn-sm" data-reject-button><?php echo JText::_('COM_EASYSOCIAL_REJECT_INVITATION_BUTTON');?></button>
		<button type="button" class="btn btn-es-primary btn-sm" data-accept-button><?php echo JText::_('COM_EASYSOCIAL_ACCEPT_INVITATION_BUTTON');?></button>
	</buttons>
</dialog>
