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

########################################
##### Configuration options.
########################################

// This should just contain fully qualified domain.
// E.g: http://yourwebsite.com or https://yourwebsite.com
$host = 'http://yourwebsite.com';

########################################

if (md5($host) == 'f0a140b2b06bad66b92e40db04642564') {
	echo "Please change the \$host value in the cron.php file to your correct url";
	exit;
}

function connect($url)
{
	$url .= '/index.php?option=com_easysocial&cron=true';

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

	// known issue
	// if the CURL response return this error "Chunky upload is not supported by HTTP 1.0"
	// then you need to set to CURL_HTTP_VERSION_1_1
	curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: close'));
	curl_setopt($ch, CURLOPT_TIMEOUT, 2);

	// known issue for some of the site
	// the cron URL will redirect to this .../index.php/en/?option=com_easysocial&cron=true
	// Once the cron URL get redirected then it will not proceed any cron script in Easysocial
	// To fix this, either change to the following URL (in this case) or add this CURLOPT_FOLLOWLOCATION set to true
	// /index.php/en/?option=com_easysocial&cron=true
	// curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

	$response = curl_exec($ch);

	curl_close($ch);
}

connect($host);

header('Content-type: application/json; UTF-8');

$obj = new stdClass();
$obj->status = 200;
$obj->message = 'Cronjob processed successfully';

echo json_encode($obj);
exit;
