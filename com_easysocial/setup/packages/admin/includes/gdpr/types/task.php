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

class SocialGdprTask extends SocialGdprAbstract
{
	public $type = 'task';
	private $tab = null;

	/**
	 * Main function to process user comment data for GDPR download.
	 *
	 * @since 2.1
	 * @access public
	 */
	public function execute(SocialGdprSection &$section)
	{
		$this->tab = $section->createTab($this);
		$tasks = $this->getTasks();

		if (!$tasks) {
			return $this->tab->finalize();
		}

		foreach ($tasks as $task) {
			$item = $this->getTemplate($task->id, $this->type);
			$item->title = ESJString::ucfirst($task->title);
			$item->intro = $this->getIntro($task);
			$item->created = $task->created;
			
			$this->tab->addItem($item);
		}
	}


	public function getTasks()
	{
		$ids = $this->tab->getProcessedIds();
		$limit = $this->getLimit();

		$model = ES::model('tasks');
		$data = $model->getTasksGdpr($this->user->id, $ids, $limit);
		$tasks = array();

		if ($data) {
			foreach ($data as $row) {
				$task = ES::table('Task');
				$task->bind($row);
				$tasks[] = $task;
			}

		}
		return $tasks;
	}

	public function getIntro($task)
	{
		$date = ES::date($task->created);

		ob_start();
		?>
		<div class="gdpr-item__desc">
			<?php echo $task->description; ?>
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
