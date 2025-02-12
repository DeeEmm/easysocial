<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2014 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined( '_JEXEC' ) or die( 'Unauthorized Access' );
?>
<div>
	<a id="<?php echo $this->privacy_element_name; ?>" class="browseButton" href="javascript:void(0);" data-index="<?php echo $this->index; ?>">Browse</a>
</div>
<ul id="privacy_ul<?php echo $this->index; ?>">
<?php
	if( $this->custom_data ) {
		foreach( $this->custom_data as $pitem )
		{
			$this->set('name', $pitem->name);
			$this->set('userid', $pitem->user_id);
			$this->set('eleName', $this->privacy_element_name);

			echo $this->loadTemplate( 'site:/privacy/default.custom.item' );
		}
	}
?>
</ul>
