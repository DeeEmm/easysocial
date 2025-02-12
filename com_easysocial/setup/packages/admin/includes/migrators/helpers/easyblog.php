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
defined( '_JEXEC' ) or die( 'Unauthorized Access' );

// ES::import( 'admin:/inclues/migrators/helpers/info' );
require_once( SOCIAL_LIB . '/migrators/helpers/info.php' );

/**
 * DB layer for EasySocial.
 *
 * @since	1.1
 * @author	Sam <sam@stackideas.com>
 */
class SocialMigratorHelperEasyBlog
{
	// component name, e.g. com_easyblog
	var $name  			= null;

	// migtration steps
	var $steps 			= null;

	var $info  			= null;

	var $mapping 		= null;

	var $accessMapping 	= null;

	var $limit 		 	= null;

	var $userMapping  	= null;

	public function __construct()
	{
		$this->info     = new SocialMigratorHelperInfo();
		$this->name  	= 'com_easyblog';

		$this->limit 	= 10; //10 items per cycle

		$this->steps[] 	= 'blog';
		$this->steps[] 	= 'comment';

		$this->accessMapping = array(
			'0' 	=> SOCIAL_PRIVACY_PUBLIC,
			'1'		=> SOCIAL_PRIVACY_MEMBER,
			'10'	=> SOCIAL_PRIVACY_MEMBER,
			'20'	=> SOCIAL_PRIVACY_MEMBER,
			'30'	=> SOCIAL_PRIVACY_FRIEND,
			'40'	=> SOCIAL_PRIVACY_ONLY_ME
			);
	}

	public function getVersion()
	{
		if ( !$this->isComponentExist()) {
			return false;
		}

		// check JomSocial version.
		$xml		= JPATH_ROOT . '/administrator/components/com_easyblog/easyblog.xml';

		$parser = ES::get( 'Parser' );
		$parser->load( $xml );

		$version	= $parser->xpath( 'version' );
		$version 	= (float) $version[0];

		return $version;
	}

	public function isInstalled()
	{
		$file = JPATH_ROOT . '/administrator/components/com_easyblog/includes/easyblog.php';

		if (! JFile::exists($file)) {
			return false;
		}

		return true;
	}

	/*
	 * return object with :
	 *     isvalid  : true or false
	 *     messsage : string.
	 *     count    : integer. item count to be processed.
	 */
	public function isComponentExist()
	{
		$obj = new stdClass();
		$obj->isvalid = false;
		$obj->count   = 0;
		$obj->message = '';

		$file = JPATH_ROOT . '/administrator/components/com_easyblog/includes/easyblog.php';

		if (! JFile::exists($file)) {
			$obj->message = 'EasyBlog not found in your site. Process aborted.';
			return $obj;
		}

		// @todo check if the db tables exists or not.


		// all pass. return object

		$obj->isvalid = true;
		$obj->count   = $this->getItemCount();

		return $obj;
	}

	public function setUserMapping( $maps )
	{
		// do nothing.
	}

	public function getItemCount()
	{
		$db = ES::db();
		$sql = $db->sql();

		$total = count( $this->steps );

		// blog posts
		$query = 'select count(1) as `total`';
		$query .= ' from `#__easyblog_post` as a';
		$query .= ' where not exists ( ';
		$query .= '		select b.`id` from `#__social_migrators` as b';
		$query .= ' 			where a.`id` = b.`oid` and b.`element` = ' . $db->Quote( 'blog' ) . ' and b.`component` = ' . $db->Quote( $this->name );
		$query .= ' )';
		$query .= ' and a.`published` = ' . $db->Quote( '1' );

		$sql->clear();
		$sql->raw( $query );
		$db->setQuery( $sql );
		$numTotal = $db->loadResult();
		$numTotal = ( $numTotal > 0 ) ? ceil( $numTotal / $this->limit ) : 0;
		$total = $total + $numTotal;


		// comments
		$query = 'select count(1) as `total`';
		$query .= ' from `#__easyblog_comment` as a';
		$query .= ' where not exists ( ';
		$query .= '		select b.`id` from `#__social_migrators` as b';
		$query .= ' 			where a.`id` = b.`oid` and b.`element` = ' . $db->Quote( 'blogcomment' ) . ' and b.`component` = ' . $db->Quote( $this->name );
		$query .= ' )';
		$query .= ' and a.`published` = ' . $db->Quote( '1' );

		$sql->clear();
		$sql->raw( $query );
		$db->setQuery( $sql );
		$numTotal = $db->loadResult();
		$numTotal = ( $numTotal > 0 ) ? ceil( $numTotal / $this->limit ) : 0;
		$total = $total + $numTotal;

		return $total;
	}

	public function process( $item )
	{
		// @debug
		$obj = new stdClass();

		if( empty( $item ) )
		{
			$item = $this->steps[0];
		}

		$result = '';

		switch( $item )
		{
			case 'blog':
				$result = $this->processBlog();
				break;

			case 'comment':
				$result = $this->processComment();
				break;

			default:
				break;
		}

		// this is the ending part to determine if the process is already ended or not.
		if( is_null( $result ) )
		{
			$keys 		= array_keys( $this->steps, $item);
			$curSteps 	= $keys[0];

			if( isset( $this->steps[ $curSteps + 1] ) )
			{
				$item = $this->steps[ $curSteps + 1];
			}
			else
			{
				$item = null;
			}

			$obj->continue = ( is_null( $item ) ) ? false : true ;
			$obj->item 	   = $item;
			$obj->message  = ( $obj->continue ) ? 'Checking for next item to migrate....' : 'No more item found.';

			return $obj;
		}


		$obj->continue = true;
		$obj->item 	   = $item;
		$obj->message  = implode( '<br />', $result->message );

		return $obj;
	}

	private function processComment()
	{

		// $file = JPATH_ROOT . '/administrator/components/com_easyblog/includes/easyblog.php';
		// require_once( $file );

		$db 	= ES::db();
		$sql 	= $db->sql();

		$query = 'select a.*';
		$query .= ' from `#__easyblog_comment` as a';
		$query .= ' where not exists ( ';
		$query .= '		select b.`id` from `#__social_migrators` as b';
		$query .= ' 			where a.`id` = b.`oid` and b.`element` = ' . $db->Quote( 'blogcomment' ) . ' and b.`component` = ' . $db->Quote( $this->name );
		$query .= ' )';
		$query .= ' and a.`published` = ' . $db->Quote( '1' );
		$query .= ' ORDER BY a.`id` ASC';
		$query .= ' LIMIT ' . $this->limit;

		$sql->raw( $query );
		$db->setQuery( $sql );


		$ebComments = $db->loadObjectList();

		if( count( $ebComments ) <= 0 )
		{
			return null;
		}

		foreach( $ebComments as $ebComment )
		{

			// add stream.
			$this->addCommentStream( $ebComment );

			// add log
			$this->log( 'blogcomment', $ebComment->id, $ebComment->id );

			$this->info->setInfo( 'Blog comment with id \'' . $ebComment->id . '\' processed succussfully.' );
		}

		return $this->info;


	}

	private function processBlog()
	{
		$file = JPATH_ROOT . '/administrator/components/com_easyblog/includes/easyblog.php';
		require_once($file);

		$db 	= ES::db();
		$sql 	= $db->sql();

		$query = 'select a.*';
		$query .= ' from `#__easyblog_post` as a';
		$query .= ' where not exists ( ';
		$query .= '		select b.`id` from `#__social_migrators` as b';
		$query .= ' 			where a.`id` = b.`oid` and b.`element` = ' . $db->Quote('blog') . ' and b.`component` = ' . $db->Quote($this->name);
		$query .= ' )';
		$query .= ' and a.`published` = ' . $db->Quote('1');
		$query .= ' ORDER BY a.`id` ASC';
		$query .= ' LIMIT ' . $this->limit;

		$sql->raw($query);
		$db->setQuery($sql);


		$ebPosts = $db->loadObjectList();

		if(count($ebPosts) <= 0)
		{
			return null;
		}

		foreach($ebPosts as $ebPost)
		{
			// $blog = EasyBlogHelper::getTable( 'Blog' , 'Table' );
			$blog = EB::post($ebPost->id);
			$blog->bind($ebPost);

			// add stream.
			$this->addBlogStream($blog);

			// add privacy.
			$this->addItemPrivacy('easyblog.blog.view', $blog->id, 'blog', $blog->created_by, $blog->access);

			// add indexer.
			$this->addBlogIndexer($blog);

			// add log
			$this->log('blog', $blog->id, $blog->id);

			$this->info->setInfo('Blog post with id \'' . $blog->id . '\' processed successfully.');
		}

		return $this->info;
	}

	private function addCommentStream( $comment )
	{
		$stream 	= ES::stream();
		$streamTemplate 	= $stream->getTemplate();

		// Get the stream template
		$streamTemplate->setActor( $comment->created_by , SOCIAL_TYPE_USER );
		$streamTemplate->setContext( $comment->id , 'blog' );
		$streamTemplate->setContent( $comment->comment );

		$streamTemplate->setVerb( 'create.comment' );
		$streamTemplate->setDate( $comment->created );

		$streamTemplate->setAccess('core.view');


		$state 	= $stream->add( $streamTemplate );
	}



	private function addBlogStream($blog)
	{
		$stream = ES::stream();
		$streamTemplate = $stream->getTemplate();

		// Set the actor.
		$streamTemplate->setActor($blog->created_by, SOCIAL_TYPE_USER);

		// Set the context.
		$streamTemplate->setContext($blog->id, 'blog');

		// Set the verb.
		$streamTemplate->setVerb('create');

		// set stream content
		$streamTemplate->setContent($blog->intro . $blog->content);

		$postDateSource = EB::config()->get('integrations_easysocial_stream_date_source');
		$streamDate = isset($blog->$postDateSource) ? $blog->$postDateSource : $blog->created;

		// set the stream creation date
		$streamTemplate->setDate($streamDate);

		// set if this is public stream based on the privacy rule.
		$streamTemplate->setAccess('easyblog.blog.view', $blog->access);

		// Create the stream data.
		$stream->add($streamTemplate);
	}


	private function addBlogIndexer( $blog )
	{
		$config = EB::config();

		$indexer 	= ES::get( 'Indexer', 'com_easyblog' );
		$template 	= $indexer->getTemplate();

		// getting the blog content
		$content 	= $blog->intro . $blog->content;

		$postLib = EB::post($blog->id);


		$image 		= '';
		// @rule: Try to get the blog image.
		if( $blog->getImage() )
		{
			$image 	= $blog->getImage('thumbnail');
		}

		if( empty( $image ) )
		{
			// @rule: Match images from blog post
			$pattern	= '/<\s*img [^\>]*src\s*=\s*[\""\']?([^\""\'\s>]*)/i';
			preg_match( $pattern , $content , $matches );

			$image		= '';

			if( $matches )
			{
				$image		= isset( $matches[1] ) ? $matches[1] : '';

				if( ESJString::stristr( $matches[1], 'https://' ) === false && ESJString::stristr( $matches[1], 'http://' ) === false && !empty( $image ) )
				{
					$image	= rtrim(JURI::root(), '/') . '/' . ltrim( $image, '/');
				}
			}
		}

		if(! $image )
		{
			$image = rtrim( JURI::root() , '/' ) . '/components/com_easyblog/assets/images/default_facebook.png';
		}

		$content    = strip_tags( $content );

		if( ESJString::strlen( $content ) > $config->get( 'integrations_easysocial_indexer_newpost_length', 250 ) )
		{
			$content = ESJString::substr( $content, 0, $config->get( 'integrations_easysocial_indexer_newpost_length', 250 ) );
		}
		$template->setContent( $blog->title, $content );

		$url	= EBR::_('index.php?option=com_easyblog&view=entry&id='.$blog->id);

		$url 	= '/' . ltrim( $url , '/' );
		$url 	= str_replace('/administrator/', '/', $url );

		$template->setSource( $blog->id, 'blog', $blog->created_by, $url);

		$template->setThumbnail( $image );

		$template->setLastUpdate( $blog->modified );

		$state = $indexer->index( $template );
		return $state;
	}




	private function addItemPrivacy( $command, $esUid, $esUType, $ebUserId, $ebAccess )
	{
		static $defaultESPrivacy = array();

		$db 	= ES::db();
		$sql 	= $db->sql();


		if(! isset( $defaultESPrivacy[ $command ] ) )
		{
			$db 	= ES::db();
			$sql 	= $db->sql();

			$commands = explode( '.', $command );
			$element = array_shift( $commands );
			$rule  	 = implode( '.', $commands );

			$query = 'select `id`, `value` from `#__social_privacy`';
			$query .= ' where `type` = ' . $db->Quote( $element );
			$query .= ' and `rule` = ' . $db->Quote( $rule );

			$sql->raw( $query );
			$db->setQuery( $sql );

			$defaultESPrivacy[ $command ] = $db->loadObject();
		}

		$defaultPrivacy = $defaultESPrivacy[ $command ];

		$privacyValue = ( isset( $this->accessMapping[ $ebAccess ] ) ) ? $this->accessMapping[ $ebAccess ] : $defaultPrivacy->value;


		$esPrivacyItem = ES::table( 'PrivacyItems' );

		$esPrivacyItem->privacy_id 	= $defaultPrivacy->id;
		$esPrivacyItem->user_id 	= $ebUserId;
		$esPrivacyItem->uid 		= $esUid;
		$esPrivacyItem->type 		= $esUType;
		$esPrivacyItem->value 		= $privacyValue;

		$esPrivacyItem->store();

	}


	public function log( $element, $oriId, $newId )
	{
		$tbl = ES::table( 'Migrators' );

		$tbl->oid 		= $oriId;
		$tbl->element 	= $element;
		$tbl->component = $this->name;
		$tbl->uid 		= $newId;
		$tbl->created 	= ES::date()->toMySQL();

		$tbl->store();
	}

}
