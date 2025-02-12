<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2016 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<label class="o-control-label">
	<span data-required 
		<?php if( ( !isset( $options['required'] ) && !$params->get( 'required' ) ) || ( isset( $options['required'] ) && !$options['required'] ) ) { ?>
		style="display: none;"
		<?php } ?>
	><?php echo JText::_('COM_EASYSOCIAL_REGISTRATION_REQUIRED_SYMBOL'); ?></span>
	
	<span data-display-title data-title <?php echo !$params->get('display_title') ? 'style="display:none;"' : '';?>><?php echo JText::_($params->get('title')); ?>:</span>
</label>
