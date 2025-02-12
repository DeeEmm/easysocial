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

class SocialGdprEvent extends SocialGdprAbstract
{
	public $type = 'event';

	/**
	 * Main function to process user post data for GDPR download.
	 *
	 * @since 5.2
	 * @access public
	 */
	public function execute(SocialGdprSection &$section)
	{
		$this->tab = $section->createTab($this);

		$events = $this->getEvents();

		if (!$events) {
			$this->tab->finalize();
			return;
		}

		foreach ($events as $event) {
			$item = $this->getTemplate($event->id, $this->type);

			$item->created = $event->created;
			$item->title = $event->title;
			$item->intro = $this->getIntro($event);
			$item->view = true;
			$item->content = $this->getContent($event);

			$this->tab->addItem($item);
		}
	}

	public function getEvents()
	{
		$ids = $this->tab->getProcessedIds();

		$options = array();
		$options['userid'] = $this->user->id;
		$options['exclusion'] = $ids;
		$options['limit'] = $this->getLimit();

		$model = ES::model('events');
		$events = $model->getEventGDPR($options);

		return $events;
	}

	public function getIntro($event)
	{
		$date = ES::date($event->created);
		ob_start();
		?>
		<div class="gdpr-item__meta">
			<?php echo $date->format($this->getDateFormat());?>
		</div>
		<?php
		$contents = ob_get_contents();
		ob_end_clean();

		return $contents;
	}

	public function getContent($event)
	{
		$event = ES::cluster($event->cluster_type, $event->id);
		$date = ES::date($event->created);

		ob_start();
		?>
		<div class="gdpr-item__desc">
			<?php echo $event->getDescription();?>
		</div>
		<div class="gdpr-item__meta">
			<?php echo $date->format($this->getDateFormat());?>
		</div>
		<?php
		$contents = ob_get_contents();
		ob_end_clean();

		return $contents;
	}
}
