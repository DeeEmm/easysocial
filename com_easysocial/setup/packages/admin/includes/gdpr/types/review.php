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

class SocialGdprReview extends SocialGdprAbstract
{
	public $type = 'review';

	/**
	 * Main function to process user reviews data for GDPR download.
	 *
	 * @since 2.2
	 * @access public
	 */
	public function execute(SocialGdprSection &$section)
	{
		$this->tab = $section->createTab($this);

		$reviews = $this->getItems();

		if (!$reviews) {
			return $this->tab->finalize();
		}

		foreach ($reviews as $review) {
			$item = $this->getTemplate($review->id, $this->type);
			$item->title = $this->getTitle($review);
			$item->preview = $this->getIntro($review);
			$item->intro = $item->preview;
			$item->created = $review->created;

			$this->tab->addItem($item);
		}
	}

	public function getItems()
	{
		// Get a list of ids that are already processed
		$ids = $this->tab->getProcessedIds();

		$model = ES::model('Reviews');
		$options = array();

		$options['userid'] = $this->user->id;
		$options['exclusion'] = $ids;
		$options['limit'] = $this->getLimit();

		$reviews = $model->getReviewsGDPR($options);

		return $reviews;
	}

	public function getTitle($review)
	{
		$cluster = ES::cluster($review->type, $review->uid);

		// prevent those review data associated group which not exist on the site
		if (!$cluster->id) {
			$title = JText::sprintf('COM_ES_CLUSTERS_NOT_EXISTED', $review->type);
			return $title;
		}

		$clusterTitle = $cluster->getTitle();

		$title = JText::sprintf('COM_ES_GDPR_SUBMITTED_REVIEW_ON', $this->user->getName(), $clusterTitle);
		return $title;
	}

	public function getIntro($review) 
	{
		$date = ES::date($review->created);
		$message = $review->title . '<br />' . $review->message;

		ob_start();
		?>
		<div class="gdpr-item__rating">
			<?php echo $this->formatRating($review->value); ?>	
		</div>	
		<div class="gdpr-item__desc">
			<?php echo $message; ?>
		</div>
		<div class="gdpr-item__meta">
			<?php echo $date->format($this->getDateFormat());?>
		</div>

		<?php
		$contents = ob_get_contents();
		ob_end_clean();

		return $contents;
	}

	public function formatRating($ratingValue)
	{
		ob_start();
		?>
			<div class="stars" data-stars="<?php echo $ratingValue; ?>">
			    <svg height="25" width="23" class="star rating" data-rating="1">
			    <polygon points="9.9, 1.1, 3.3, 21.78, 19.8, 8.58, 0, 8.58, 16.5, 21.78" style="fill-rule:nonzero;"/>
			    </svg>
			    <svg height="25" width="23" class="star rating" data-rating="2">
			    <polygon points="9.9, 1.1, 3.3, 21.78, 19.8, 8.58, 0, 8.58, 16.5, 21.78" style="fill-rule:nonzero;"/>
			    </svg>
			    <svg height="25" width="23" class="star rating" data-rating="3">
			    <polygon points="9.9, 1.1, 3.3, 21.78, 19.8, 8.58, 0, 8.58, 16.5, 21.78" style="fill-rule:nonzero;"/>
			    </svg>
			    <svg height="25" width="23" class="star rating" data-rating="4">
			    <polygon points="9.9, 1.1, 3.3, 21.78, 19.8, 8.58, 0, 8.58, 16.5, 21.78" style="fill-rule:nonzero;"/>
			    </svg>
			    <svg height="25" width="23" class="star rating" data-rating="5">
			    <polygon points="9.9, 1.1, 3.3, 21.78, 19.8, 8.58, 0, 8.58, 16.5, 21.78" style="fill-rule:nonzero;"/>
			    </svg>
			</div>
		<?php
		$contents = ob_get_contents();
		ob_end_clean();		

		return $contents;
	}
}
