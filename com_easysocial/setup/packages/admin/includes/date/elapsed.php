 <?php
 /**
 * @package %PACKAGE%
 * @subpackage %FIELD.SUBPACKAGE%
 * @license GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

class SocialDateElapsed
{
	public $prefixAgo = "";

	public $prefixFromNow = "";

	public $suffixAgo = "ago";

	public $suffixFromNow = "from now";

	public $numbers = array();

	public function seconds() {
		return "less than a minute";
	}

	public function minute() {
		return "about a minute";
	}

	public function minutes() {
		return "%d minutes";
	}

	public function hour() {
		return "about an hour";
	}

	public function hours() {
		return "about %d hours";
	}

	public function day() {
		return "a day";
	}

	public function days() {
		return "%d days";
	}

	public function month() {
		return "about a month";
	}

	public function months() {
		return "%d months";
	}

	public function year() {
		return "about a year";
	}

	public function years() {
		return "%d years";
	}

	public function wordSeparator() {
		return " ";
	}

}
