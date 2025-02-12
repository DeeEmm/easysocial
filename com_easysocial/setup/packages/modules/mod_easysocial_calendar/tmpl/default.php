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
<div id="es" class="mod-es mod-es-calendar<?php echo $lib->getSuffix();?>" data-calendar-module data-filter="<?php echo $filter; ?>" data-categoryid="<?php echo $categoryId; ?>" data-clusterid="<?php echo $clusterId; ?>">
	<div>
		<div data-events-calendar-module></div>
	</div>
</div>

<script>
EasySocial.require().script('site/events/filter')
.done(function($) {
	$('[data-calendar-module]').implement(EasySocial.Controller.Events.Filter, {
		isModule: '<?php echo $useRealHyperlinks; ?>'
	});
});
</script>
