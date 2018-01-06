<?php
/**
 * @package   angi4j
 * @copyright Copyright (C) 2009-2017 Nicholas K. Dionysopoulos. All rights reserved.
 * @author    Nicholas K. Dionysopoulos - http://www.dionysopoulos.me
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

defined('_AKEEBA') or die();

class AngieModelWordpressReplacedata extends AModel
{
	/** @var ADatabaseDriver Reference to the database driver object */
	private $db = null;

	/** @var array The tables we have to work on: (table, method, fields) */
	protected $tables = array();

	/** @var string The current table being processed */
	protected $currentTable = null;

	/** @var int The current row being processed */
	protected $currentRow = null;

	/** @var int The total rows in the table being processed */
	protected $totalRows = null;

	/** @var array The replacements to conduct */
	protected $replacements = array();

	/** @var int How many rows to process at once */
	protected $batchSize = 100;

	/** @var null|ATimer The timer used to step the engine */
	protected $timer = null;

	/** @var int Minimum execution time (in seconds) */
	protected $min_exec = 0;

	/** @var int Maximum execution time (in seconds) */
	protected $max_exec = 3;

	/** @var int Runtime bias */
	protected $bias = 75;

	/**
	 * Get a reference to the database driver object
	 *
	 * @return ADatabaseDriver
	 */
	public function &getDbo()
	{
		if ( !is_object($this->db))
		{
			/** @var AngieModelDatabase $model */
			$model      = AModel::getAnInstance('Database', 'AngieModel', array(), $this->container);
			$keys       = $model->getDatabaseNames();
			$firstDbKey = array_shift($keys);

			$connectionVars = $model->getDatabaseInfo($firstDbKey);
			$name           = $connectionVars->dbtype;

			$options = array(
				'database' => $connectionVars->dbname,
				'select'   => 1,
				'host'     => $connectionVars->dbhost,
				'user'     => $connectionVars->dbuser,
				'password' => $connectionVars->dbpass,
				'prefix'   => $connectionVars->prefix,
			);

			$this->db = ADatabaseFactory::getInstance()->getDriver($name, $options);
			$this->db->setUTF();
		}

		return $this->db;
	}

	/**
	 * Get the data replacement values
	 *
	 * @param   bool  $fromRequest  Should I override session data with those from the request?
	 * @param   bool  $force        True to forcibly load the default replacements.
	 *
	 * @return array
	 */
	public function getReplacements($fromRequest = false, $force = false)
	{
		$session      = $this->container->session;
		$replacements = $session->get('dataReplacements', array());

		if (empty($replacements))
		{
			$replacements = array();
		}

		if ($fromRequest)
		{
			$replacements = array();

			$keys   = trim($this->input->get('replaceFrom', '', 'string', 2));
			$values = trim($this->input->get('replaceTo', '', 'string', 2));

			if ( !empty($keys))
			{
				$keys   = explode("\n", $keys);
				$values = explode("\n", $values);

				foreach ($keys as $k => $v)
				{
					if ( !isset($values[$k]))
					{
						continue;
					}

					$replacements[$v] = $values[$k];
				}
			}
		}

		if (empty($replacements) || $force)
		{
			$replacements = $this->getDefaultReplacements();
		}

		/**
		 * I must not replace / with something else, e.g. /foobar. This would cause URLs such as
		 * http://www.example.com/something to be replaced with a monstrosity like
		 * http:/foobar/foobar/www.example.com/foobarsomething which breaks the site :s
		 *
		 * The same goes for the .htaccess file, where /foobar would be added in random places,
		 * breaking the site.
		 */
		if (isset($replacements['/']))
		{
			unset($replacements['/']);
		}

		$session->set('dataReplacements', $replacements);

		return $replacements;
	}

	/**
	 * Returns all the database tables which are not part of the WordPress core
	 *
	 * @return array
	 */
	public function getNonCoreTables()
	{
		// Get a list of core tables
		$coreTables = array(
			'#__commentmeta', '#__comments', '#__links', '#__options', '#__postmeta', '#__posts',
			'#__term_relationships', '#__term_taxonomy', '#__terms', '#__usermeta', '#__users',
		);

		$db = $this->getDbo();

		if ($this->isMultisite())
		{
			$additionalTables = array('#__blogs', '#__site', '#__sitemeta');

			/** @var AngieModelWordpressConfiguration $config */
			$config = AModel::getAnInstance('Configuration', 'AngieModel', array(), $this->container);
			$mainBlogId = $config->get('blog_id_current_site', 1);

			$map     = $this->getMultisiteMap($db);
			$siteIds = array_keys($map);

			foreach ($siteIds as $id)
			{
				if ($id == $mainBlogId)
				{
					continue;
				}

				foreach ($coreTables as $table)
				{
					$additionalTables[] = str_replace('#__', '#__' . $id . '_', $table);
				}
			}

			$coreTables = array_merge($coreTables, $additionalTables);
		}

		// Now get a list of non-core tables
		$prefix       = $db->getPrefix();
		$prefixLength = strlen($prefix);
		$allTables    = $db->getTableList();

		$result = array();

		foreach ($allTables as $table)
		{
			if (substr($table, 0, $prefixLength) == $prefix)
			{
				$table = '#__' . substr($table, $prefixLength);
			}

			if (in_array($table, $coreTables))
			{
				continue;
			}

			$result[] = $table;
		}

		return $result;
	}

	/**
	 * Loads the engine status off the session
	 */
	public function loadEngineStatus()
	{
		$session = $this->container->session;

		$this->replacements = $this->getReplacements();
		$this->tables       = $session->get('replacedata.tables', array());
		$this->currentTable = $session->get('replacedata.currentTable', null);
		$this->currentRow   = $session->get('replacedata.currentRow', 0);
		$this->totalRows    = $session->get('replacedata.totalRows', null);
		$this->batchSize	= $session->get('replacedata.batchSize', 100);
		$this->min_exec		= $session->get('replacedata.min_exec', 0);
		$this->max_exec		= $session->get('replacedata.max_exec', 3);
		$this->bias         = $session->get('replacedata.bias', 75);
	}

	/**
	 * Saves the engine status to the session
	 */
	public function saveEngineStatus()
	{
		$session = $this->container->session;

		$session->set('replacedata.tables', $this->tables);
		$session->set('replacedata.currentTable', $this->currentTable);
		$session->set('replacedata.currentRow', $this->currentRow);
		$session->set('replacedata.totalRows', $this->totalRows);
		$session->set('replacedata.batchSize', $this->batchSize);
		$session->set('replacedata.min_exec', $this->min_exec);
		$session->set('replacedata.max_exec', $this->max_exec);
		$session->set('replacedata.bias', $this->bias);
	}

	/**
	 * Initialises the replacement engine
	 */
	public function initEngine()
	{
		// Get the replacements to be made
		$this->replacements = $this->getReplacements(true);

		// Add the default core tables
		$this->tables = array(
			array(
				'table'  => '#__comments',
				'method' => 'simple', 'fields' => array('comment_author_url', 'comment_content')
			),
			array(
				'table'  => '#__links',
				'method' => 'simple', 'fields' => array('link_url', 'link_image', 'link_rss'),
			),
			array(
				'table'  => '#__posts',
				'method' => 'simple', 'fields' => array('post_content', 'post_excerpt', 'guid'),
			),
			array(
				'table'  => '#__commentmeta',
				'method' => 'serialised', 'fields' => array('meta_value'),
			),
			array(
				'table'  => '#__options',
				'method' => 'serialised', 'fields' => array('option_value'),
			),
			array(
				'table'  => '#__postmeta',
				'method' => 'serialised', 'fields' => array('meta_value'),
			),
			array(
				'table'  => '#__usermeta',
				'method' => 'serialised', 'fields' => array('meta_value'),
			),
		);

		// Add multisite tables if this is a multisite installation
		$db = $this->getDbo();

		if ($this->isMultisite())
		{
			/** @var AngieModelWordpressConfiguration $config */
			$config = AModel::getAnInstance('Configuration', 'AngieModel', array(), $this->container);
			$mainBlogId = $config->get('blog_id_current_site', 1);

			// First add the default core tables which are duplicated for each additional blog in the blog network
			$tables = array_merge($this->tables);
			$map    = $this->getMultisiteMap($db);

			// Run for each site in the blog network with an ID ≠ 1
			foreach ($map as $blogId => $blogPathInfo)
			{
				if ($blogId == $mainBlogId)
				{
					// This is the master site of the network; it doesn't have duplicated tables
					continue;
				}

				$blogPrefix = '#__' . $blogId . '_';

				foreach ($tables as $originalTable)
				{
					// Some tables only exist in the network master installation and must be ignored
					if (in_array($originalTable['table'], array('#__usermeta')))
					{
						continue;
					}

					// Translate the table definition
					$tableDefinition = array(
						'table'  => str_replace('#__', $blogPrefix, $originalTable['table']),
						'method' => $originalTable['method'],
						'fields' => $originalTable['fields']
					);

					// Add it to the table list
					$this->tables[] = $tableDefinition;
				}
			}

			// Finally, add some core tables which are only present in a blog network's master site
			$this->tables[] = array(
				'table'  => '#__site',
				'method' => 'simple', 'fields' => array('domain', 'path')
			);

			/**
			 * IMPORTANT! We must NOT change #__blogs here. It needs special handling in updateMultisiteTables().
			 *
			 * The special handling is required because we may have to convert from a subdomain to a subdirectory
			 * installation.
			 */
			/**
			$this->tables[] = array(
				'table'  => '#__blogs',
				'method' => 'simple', 'fields' => array('domain', 'path')
			);
			/**/

			$this->tables[] = array(
				'table'  => '#__sitemeta',
				'method' => 'serialised', 'fields' => array('meta_value'),
			);

		}

		// Get any additional tables
		$extraTables = $this->input->get('extraTables', array(), 'array');

		if ( !empty($extraTables) && is_array($extraTables))
		{
			foreach ($extraTables as $table)
			{
				$this->tables[] = array('table' => $table, 'method' => 'serialised', 'fields' => null);
			}
		}

		// Intialise the engine state
		$this->currentTable = null;
		$this->currentRow   = null;
		$this->fields       = null;
		$this->totalRows    = null;
		$this->batchSize	= $this->input->getInt('batchSize', 100);
		$this->min_exec     = $this->input->getInt('min_exec', 0);
		$this->max_exec		= $this->input->getInt('max_exec', 3);
		$this->bias         = $this->input->getInt('runtime_bias', 75);

		// Replace keys in #__options and #__usermeta which depend on the database table prefix, if the prefix has been changed
		// reference: http://stackoverflow.com/a/13815934/485241
		$this->timer = new ATimer($this->min_exec, $this->max_exec, $this->bias);

		/** @var AngieModelWordpressConfiguration $config */
		$config    = AModel::getAnInstance('Configuration', 'AngieModel', array(), $this->container);
		$oldPrefix = $config->get('olddbprefix');
		$newPrefix = $db->getPrefix();

		if ($oldPrefix != $newPrefix)
		{
			$optionsTables  = array('#__options');
			$usermetaTables = array('#__usermeta');

			if ($this->isMultisite())
			{
				$map     = $this->getMultisiteMap($db);
				$blogIds = array_keys($map);

				/** @var AngieModelWordpressConfiguration $config */
				$config = AModel::getAnInstance('Configuration', 'AngieModel', array(), $this->container);
				$mainBlogId = $config->get('blog_id_current_site', 1);

				foreach ($blogIds as $id)
				{
					if ($id == $mainBlogId)
					{
						continue;
					}

					$optionsTables[]  = '#__' . $id . '_options';
					$usermetaTables[] = '#__' . $id . '_usermeta';
				}
			}

			foreach ($optionsTables as $table)
			{
				$query = $db->getQuery(true)
							->update($db->qn($table))
							->set(
								$db->qn('option_name') . ' = REPLACE(' . $db->qn('option_name') . ', ' . $db->q($oldPrefix) . ', ' . $db->q($newPrefix) . ')'
							)
							->where(
								$db->qn('option_name') . ' LIKE ' . $db->q($oldPrefix . '%')
							)
							->where(
								$db->qn('option_name') . ' != REPLACE(' . $db->qn('option_name') . ', ' . $db->q($oldPrefix) . ', ' . $db->q($newPrefix) . ')'
							);

				try
				{
					$db->setQuery($query)->execute();
				}
				catch (Exception $e)
				{
					// Do nothing if the replacement fails
				}
			}

			foreach ($usermetaTables as $table)
			{
				$query = $db->getQuery(true)
					->update($db->qn($table))
					->set(
						$db->qn('meta_key') . ' = REPLACE(' . $db->qn('meta_key') . ', ' . $db->q($oldPrefix) . ', ' . $db->q($newPrefix) . ')'
					)
					->where(
						$db->qn('meta_key') . ' LIKE ' . $db->q($oldPrefix . '%')
					)
					->where(
						$db->qn('meta_key') . ' != REPLACE(' . $db->qn('meta_key') . ', ' . $db->q($oldPrefix) . ', ' . $db->q($newPrefix) . ')'
					);

				try
				{
					$db->setQuery($query)->execute();
				}
				catch (Exception $e)
				{
					// Do nothing if the replacement fails
				}
			}
		}

		// The #__blogs table used by multisite installations requires a bit of post-processing.
		if ($this->isMultisite())
		{
			$this->updateMultisiteTables();
		}

		// Finally, return and let the replacement engine run
		return array('msg' => AText::_('SETUP_LBL_REPLACEDATA_MSG_INITIALISED'), 'more' => true);
	}

	/**
	 * Performs a single step of the data replacement engine
	 *
	 * @return  array  Status of the engine (msg: error message, more: true if I need more steps)
	 */
	public function stepEngine()
	{
		if ( !is_object($this->timer) || !($this->timer instanceof ATimer))
		{
			$this->timer = new ATimer($this->min_exec, $this->max_exec, $this->bias);
		}

		$msg              = '';
		$more             = true;
		$db               = $this->getDbo();
		$serialisedHelper = new AUtilsSerialised();

		while ($this->timer->getTimeLeft() > 0)
		{
			// Are we done with all tables?
			if (is_null($this->currentTable) && empty($this->tables))
			{
				$msg  = AText::_('SETUP_LBL_REPLACEDATA_MSG_DONE');
				$more = false;

				break;
			}

			// Last table done and ready for more?
			if (is_null($this->currentTable))
			{
				$this->currentTable = array_shift($this->tables);
				$this->currentRow   = 0;

				if (empty($this->currentTable['table']))
				{
					$msg  = AText::_('SETUP_LBL_REPLACEDATA_MSG_DONE');
					$more = false;

					break;
				}

				$query = $db->getQuery(true)
							->select('COUNT(*)')->from($db->qn($this->currentTable['table']));

				try
				{
					$this->totalRows = $db->setQuery($query)->loadResult();
				}
				catch (Exception $e)
				{
					// If the table does not exist go to the next table
					$this->currentTable = null;
					continue;
				}
			}

			// Is this a simple replacement (one SQL query)?
			if ($this->currentTable['method'] == 'simple')
			{
				$msg = $this->currentTable['table'];

				// Perform the replacement
				$this->performSimpleReplacement($db);

				// Go to the next table
				$this->currentTable = null;
				continue;
			}

			// If we're done processing this table, go to the next table
			if ($this->currentRow >= $this->totalRows)
			{
				$msg = $this->currentTable['table'];

				$this->currentTable = null;
				continue;
			}

			// This is a complex replacement for serialised data. Let's get a bunch of data.
			$tableName        = $this->currentTable['table'];
			$this->currentRow = empty($this->currentRow) ? 0 : $this->currentRow;
			try
			{
				$query = $db->getQuery(true)->select('*')->from($db->qn($tableName));
				$data  = $db->setQuery($query, $this->currentRow, $this->batchSize)->loadAssocList();
			}
			catch (Exception $e)
			{
				// If the table does not exist go to the next table
				$this->currentTable = null;
				continue;
			}

			if ( !empty($data))
			{
				// Loop all rows
				foreach ($data as $row)
				{
					// Make sure we have time
					if ($this->timer->getTimeLeft() <= 0)
					{
						$msg = $this->currentTable['table'] . ' ' . $this->currentRow . ' / ' . $this->totalRows;
						break;
					}

					// Which fields should I parse?
					if ( !empty($this->currentTable['fields']))
					{
						$fields = $this->currentTable['fields'];
					}
					else
					{
						$fields = array_keys($row);
					}

					foreach ($fields as $field)
					{
						$fieldValue   = $row[$field];
						$from         = array_keys($this->replacements);
						$to           = array_values($this->replacements);

						if ($serialisedHelper->isSerialised($fieldValue))
						{
							// Replace serialised data
							try
							{
								$decoded = $serialisedHelper->decode($fieldValue);

								$serialisedHelper->replaceTextInDecoded($decoded, $from, $to);

								$fieldValue = $serialisedHelper->encode($decoded);
							}
							catch (Exception $e)
							{
								// Yeah, well...
							}
						}
						else
						{
							// Replace text data
							$fieldValue = str_replace($from, $to, $fieldValue);
						}

						$row[$field] = $fieldValue;
					}

					$row = array_map(array($db, 'quote'), $row);

					$query = $db->getQuery(true)->replace($db->qn($tableName))
								->columns(array_keys($row))
								->values(implode(',', $row));

					try
					{
						$db->setQuery($query)->execute();
					}
					catch (Exception $e)
					{
						// If there's no primary key the replacement will fail. Oh, well, what the hell...
					}

					$this->currentRow++;
				}
			}
		}

		// Am I done with DB replacement? If so let's update some files
		if (!$more)
		{
			$this->updateFiles();
		}

		// Sleep if we didn't hit the minimum execution time
		$this->timer->enforce_min_exec_time();

		return array('msg' => $msg, 'more' => $more);
	}

	/**
	 * Returns the default replacement values
	 *
	 * @return array
	 */
	protected function getDefaultReplacements()
	{
		$replacements = array();

		/** @var AngieModelWordpressConfiguration $config */
		$config = AModel::getAnInstance('Configuration', 'AngieModel', array(), $this->container);

		// Main site's URL
		$newReplacements = $this->getDefaultReplacementsForMainSite($config);
		$replacements    = array_merge($replacements, $newReplacements);

		// Multisite's URLs
		$newReplacements = $this->getDefaultReplacementsForMultisite($config);
		$replacements    = array_merge($replacements, $newReplacements);

		// Database prefix
		$newReplacements = $this->getDefaultReplacementsForDbPrefix($config);
		$replacements    = array_merge($replacements, $newReplacements);

		// Take into account JSON-encoded data
		foreach ($replacements as $from => $to)
		{
			// If we don't do that we end with the string literal "null" which is incorrect
			if (is_null($to))
			{
				$to = '';
			}

			$jsonFrom = json_encode($from);
			$jsonTo   = json_encode($to);
			$jsonFrom = trim($jsonFrom, '"');
			$jsonTo   = trim($jsonTo, '"');

			if ($jsonFrom != $from)
			{
				$replacements[$jsonFrom] = $jsonTo;
			}
		}

		// All done
		return $replacements;
	}

	/**
	 * Perform a simple replacement on the current table
	 *
	 * @param ADatabaseDriver $db
	 *
	 * @return void
	 */
	protected function performSimpleReplacement($db)
	{
		$tableName = $this->currentTable['table'];

		// Run all replacements
		foreach ($this->replacements as $from => $to)
		{
			$query = $db->getQuery(true)
						->update($db->qn($tableName));

			foreach ($this->currentTable['fields'] as $field)
			{
				$query->set(
					$db->qn($field) . ' = REPLACE(' .
					$db->qn($field) . ', ' . $db->q($from) . ', ' . $db->q($to) .
					')');
			}

			try
			{
				$db->setQuery($query)->execute();
			}
			catch (Exception $e)
			{
				// Do nothing if the replacement fails
			}
		}
	}

	/**
	 * Get the map of IDs to blog URLs
	 *
	 * @param   ADatabaseDriver $db The database connection
	 *
	 * @return  array  The map, or an empty array if this is not a multisite installation
	 */
	protected function getMultisiteMap($db)
	{
		static $map = null;

		if (is_null($map))
		{
			/** @var AngieModelWordpressConfiguration $config */
			$config = AModel::getAnInstance('Configuration', 'AngieModel', array(), $this->container);

			// Which site ID should I use?
			$site_id = $config->get('site_id_current_site', 1);

			// Get all of the blogs of this site
			$query = $db->getQuery(true)
						->select(array(
							$db->qn('blog_id'),
							$db->qn('domain'),
							$db->qn('path'),
						))
						->from($db->qn('#__blogs'))
						->where($db->qn('site_id') . ' = ' . $db->q($site_id))
			;

			try
			{
				$map = $db->setQuery($query)->loadAssocList('blog_id');
			}
			catch (Exception $e)
			{
				$map = array();
			}
		}

		return $map;
	}

	/**
	 * Is this a multisite installation?
	 *
	 * @return  bool  True if this is a multisite installation
	 */
	public function isMultisite()
	{
		/** @var AngieModelWordpressConfiguration $config */
		$config = AModel::getAnInstance('Configuration', 'AngieModel', array(), $this->container);

		return $config->get('multisite', false);
	}

	/**
	 * Internal method to get the default replacements for the main site URL
	 *
	 * @param   AngieModelWordpressConfiguration $config The configuration model
	 *
	 * @return  array  Any replacements to add
	 */
	private function getDefaultReplacementsForMainSite($config)
	{
		$replacements = array();

		// These values are stored inside the session, after the setup step
		$old_url = $config->get('oldurl');
		$new_url = $config->get('homeurl');

		if ($old_url == $new_url)
		{
			return $replacements;
		}

		// Let's get the reference of the previous absolute path
		/** @var AngieModelBaseMain $mainModel */
		$mainModel  = AModel::getAnInstance('Main', 'AngieModel', array(), $this->container);
		$extra_info = $mainModel->getExtraInfo();

		if (isset($extra_info['root']) && $extra_info['root'])
		{
			$old_path = rtrim($extra_info['root']['current'], '/');
			$new_path = rtrim(APATH_SITE, '/');

			// Replace only if they are different
			if ($old_path != $new_path)
			{
				$replacements[$old_path] = $new_path;
			}
		}

		$oldUri = new AUri($old_url);
		$newUri = new AUri($new_url);
		$oldDirectory = $oldUri->getPath();
		$newDirectory = $newUri->getPath();

		// Replace domain site only if the protocol, the port or the domain are different
		if (
			($oldUri->getHost()   != $newUri->getHost()) ||
			($oldUri->getPort()   != $newUri->getPort()) ||
			($oldUri->getScheme() != $newUri->getScheme())
		)
		{
			// Normally we need to replace both the domain and path, e.g. https://www.example.com => http://localhost/wp

			$old = $oldUri->toString(array('scheme', 'host', 'port', 'path'));
			$new = $newUri->toString(array('scheme', 'host', 'port', 'path'));

			// However, if the path is the same then we must only replace the domain.
			if ($oldDirectory == $newDirectory)
			{
				$old = $oldUri->toString(array('scheme', 'host', 'port'));
				$new = $newUri->toString(array('scheme', 'host', 'port'));
			}

			$replacements[$old] = $new;

		}

		// If the relative path to the site is different, replace it too, but ONLY if the old directory isn't empty.
		if (!empty($oldDirectory) && ($oldDirectory != $newDirectory))
		{
			$replacements[$oldDirectory] = $newDirectory;
		}

		return $replacements;
	}

	/**
	 * Internal method to get the default replacements for multisite's URLs
	 *
	 * @param   AngieModelWordpressConfiguration $config The configuration model
	 *
	 * @return  array  Any replacements to add
	 */
	private function getDefaultReplacementsForMultisite($config)
	{
		$replacements = array();
		$db           = $this->getDbo();

		if (!$this->isMultisite())
		{
			return $replacements;
		}

		// These values are stored inside the session, after the setup step
		$old_url = $config->get('oldurl');
		$new_url = $config->get('homeurl');

		// If the URL didn't change do nothing
		if ($old_url == $new_url)
		{
			return $replacements;
		}

		// Get the old and new base domain and base path
		$oldUri = new AUri($old_url);
		$newUri = new AUri($new_url);

		$newDomain = $newUri->getHost();
		$oldDomain = $oldUri->getHost();

		$newPath = $newUri->getPath();
		$newPath = empty($newPath) ? '/' : $newPath;
		$oldPath = $config->get('path_current_site', $oldUri->getPath());

		$replaceDomains = $newDomain != $oldDomain;
		$replacePaths   = $oldPath != $newPath;

		// Get the multisites information
		$multiSites = $this->getMultisiteMap($db);

		// Get other information
		$mainBlogId    = $config->get('blog_id_current_site', 1);
		$useSubdomains = $config->get('subdomain_install', 0);

		/**
		 * If we use subdomains and we are restoring to a different path OR we are restoring to localhost THEN
		 * we must convert subdomains to subdirectories.
		 */
		$convertSubdomainsToSubdirs = $this->mustConvertSudomainsToSubdirs($config, $replacePaths, $newDomain);

		// Do I have to replace the domain?
		if ($oldDomain != $newDomain)
		{
			$replacements[$oldDomain] = $newUri->getHost();
		}

		// Maybe I have to do... nothing?
		if ($useSubdomains && !$replaceDomains && !$replacePaths)
		{
			return $replacements;
		}

		// Subdirectories installation and the path hasn't changed
		if (!$useSubdomains && !$replacePaths)
		{
			return $replacements;
		}

		// Loop for each multisite
		foreach ($multiSites as $blogId => $info)
		{
			// Skip the first site, it is the same as the main site
			if ($blogId == $mainBlogId)
			{
				continue;
			}

			// Multisites using subdomains?
			if ($useSubdomains && !$convertSubdomainsToSubdirs)
			{
				$blogDomain = $info['domain'];

				// Extract the subdomain
				$subdomain  = substr($blogDomain, 0, -strlen($oldDomain));

				// Add a replacement for this domain
				$replacements[$blogDomain] = $subdomain . $newDomain;

				continue;
			}

			// Convert subdomain install to subdirectory install
			if ($convertSubdomainsToSubdirs)
			{
				$blogDomain = $info['domain'];

				/**
				 * No, you don't need this. You need to convert the old subdomain to the new domain PLUS path **AND**
				 * different RewriteRules in .htaccess to magically transform invalid paths to valid paths. Bleh.
				 */
				// Convert old subdomain (blog1.example.com) to new full domain (example.net)
				// $replacements[$blogDomain] = $newUri->getHost();

				// Convert links in post GUID, e.g. //blog1.example.com/ TO //example.net/mydir/blog1/
				$subdomain  = substr($blogDomain, 0, -strlen($oldDomain) - 1);
				$from = '//' . $blogDomain;
				$to = '//' . $newUri->getHost() . $newUri->getPath() . '/' . $subdomain;
				$to = rtrim($to, '/');
				$replacements[$from] = $to;

				continue;
			}

			// Multisites using subdirectories. Let's check if I have to extract the old path.
			$path = (strpos($info['path'], $oldPath) === 0) ? substr($info['path'], strlen($oldPath)) : $info['path'];

			// Construct the new path and add it to the list of replacements
			$path                        = trim($path, '/');
			$newMSPath                   = $newPath . '/' . $path;
			$newMSPath                   = trim($newMSPath, '/');
			$replacements[$info['path']] = '/' . $newMSPath;
		}

		// Important! We have to change subdomains BEFORE the main domain. And for this, we need to reverse the
		// replacements table. If you're wondering why: old domain example.com, new domain www.example.net. This
		// makes blog1.example.com => blog1.www.example.net instead of blog1.example.net (note the extra www). Oops!
		$replacements = array_reverse($replacements);

		return $replacements;
	}

	/**
	 * Internal method to get the default replacements for the database prefix
	 *
	 * @param   AngieModelWordpressConfiguration $config The configuration model
	 *
	 * @return  array  Any replacements to add
	 */
	private function getDefaultReplacementsForDbPrefix($config)
	{
		$replacements = array();

		// Replace the table prefix if it's different
		$db        = $this->getDbo();
		$oldPrefix = $config->get('olddbprefix');
		$newPrefix = $db->getPrefix();

		if ($oldPrefix != $newPrefix)
		{
			$replacements[$oldPrefix] = $newPrefix;

			return $replacements;
		}

		return $replacements;
	}

	/**
	 * Removes the subdomain from a full domain name. For example:
	 * removeSubdomain('www.example.com') = 'example.com'
	 * removeSubdomain('example.com') = 'example.com'
	 * removeSubdomain('localhost.localdomain') = 'localhost.localdomain'
	 * removeSubdomain('foobar.localhost.localdomain') = 'localhost.localdomain'
	 * removeSubdomain('localhost') = 'localhost'
	 *
	 * @param   string  $domain  The domain to remove its subdomain
	 *
	 * @return  string
	 */
	private function removeSubdomain($domain)
	{
		$domain = trim($domain, '.');

		$parts = explode('.', $domain);

		if (count($parts) > 2)
		{
			array_shift($parts);
		}

		return implode('.', $parts);
	}

	/**
	 * Updates known files that are storing absolute paths inside them
	 */
	private function updateFiles()
	{
		$files = array(
			// Do not replace anything in .htaccess; we'll do that in the next (finalize) step of the restoration.
			/**
			APATH_SITE.'/.htaccess',
			APATH_SITE.'/htaccess.bak',
			/**/
			// I'll try to apply the changes to those files and their "backup" counterpart
			APATH_SITE.'/.user.ini.bak',
			APATH_SITE.'/.user.ini',
			APATH_SITE.'/php.ini',
			APATH_SITE.'/php.ini.bak',
			// Wordfence is storing the absolute path inside their file. Because __DIR__ is too mainstream..
			APATH_SITE.'/wordfence-waf.php',
		);

		foreach ($files as $file)
		{
			if (!file_exists($file))
			{
				continue;
			}

			$contents = file_get_contents($file);

			foreach ($this->replacements as $from => $to)
			{
				$contents = str_replace($from, $to, $contents);
			}

			file_put_contents($file, $contents);
		}
	}

	/**
	 * Post-processing for the #__blogs table of multisite installations
	 */
	public function updateMultisiteTables()
	{
		// Get the new base domain and base path

		/** @var AngieModelWordpressConfiguration $config */
		$config                     = AModel::getAnInstance('Configuration', 'AngieModel', array(), $this->container);
		$new_url                    = $config->get('homeurl');
		$newUri                     = new AUri($new_url);
		$newDomain                  = $newUri->getHost();
		$newPath                    = $newUri->getPath();
		$old_url                    = $config->get('oldurl');
		$oldUri                     = new AUri($old_url);
		$oldDomain                  = $oldUri->getHost();
		$oldPath                    = $oldUri->getPath();
		$useSubdomains              = $config->get('subdomain_install', 0);
		$changedDomain              = $newUri->getHost() != $oldDomain;
		$changedPath                = $oldPath != $newPath;
		$convertSubdomainsToSubdirs = $this->mustConvertSudomainsToSubdirs($config, $changedPath, $newDomain);

		$db = $this->getDbo();

		$query = $db->getQuery(true)
			->select('*')
			->from($db->qn('#__blogs'));

		try
		{
			$blogs = $db->setQuery($query)->loadObjectList();
		}
		catch (Exception $e)
		{
			return;
		}

		foreach ($blogs as $blog)
		{
			if ($blog->blog_id == 1)
			{
				// Default site: path must match the site's installation path (e.g. /foobar/)
				$blog->path = '/' . trim($newPath, '/') . '/';
			}

			/**
			 * Converting blog1.example.com to www.example.net/myfolder/blog1 (multisite subdomain installation in the
			 * site's root TO multisite subfolder installation in a subdirectory)
			 */
			if ($convertSubdomainsToSubdirs)
			{
				// Extract the subdomain WITHOUT the trailing dot
				$subdomain = substr($blog->domain, 0, -strlen($oldDomain)-1);

				// Step 1. domain: Convert old subdomain (blog1.example.com) to new full domain (www.example.net)
				$blog->domain = $newUri->getHost();

				// Step 2. path: Replace old path (/) with new path + slug (/mysite/blog1).
				$blogPath   = trim($newPath, '/') . '/' . trim($subdomain, '/') . '/';
				$blog->path = '/' . ltrim($blogPath, '/') . '/';

				if ($blog->path == '//')
				{
					$blog->path = '/';
				}
			}
			/**
			 * Converting blog1.example.com to blog1.example.net (keep multisite subdomain installation, change the
			 * domain name)
			 */
			elseif ($useSubdomains && $changedDomain)
			{
				// Change domain (extract subdomain a.k.a. alias, append $newDomain to it)
				$subdomain    = substr($blog->domain, 0, -strlen($oldDomain));
				$blog->domain = $subdomain . $newDomain;
			}
			/**
			 * Convert subdomain installations when EITHER the domain OR the path have changed. E.g.:
			 *  www.example.com/blog1   to  www.example.net/blog1
			 * OR
			 *  www.example.com/foo/blog1   to  www.example.com/bar/blog1
			 * OR
			 *  www.example.com/foo/blog1   to  www.example.net/bar/blog1
			 */
			elseif ($changedDomain || $changedPath)
			{
				if ($changedDomain)
				{
					// Update the domain
					$blog->domain = $newUri->getHost();
				}

				if ($changedPath)
				{
					// Change $blog->path (remove old path, keep alias, prefix it with new path)
					$path       = (strpos($blog->path, $oldPath) === 0) ? substr($blog->path, strlen($oldPath)) : $blog->path;
					$blog->path = '/' . trim($newPath . '/' . ltrim($path, '/'), '/');
				}
			}

			// For every record, make sure the path column ends in forward slash (required by WP)
			$blog->path = rtrim($blog->path, '/') . '/';

			// Save the changed record
			try
			{
				$db->updateObject('#__blogs', $blog, array('blog_id', 'site_id'));
			}
			catch (Exception $e)
			{
				// If we failed to save the record just skip over to the next one.
			}
		}

		// Finally, update the wp-config.php file
		$this->updateWPConfigFile($config);
	}

	/**
	 * Do I have to convert the subdomain installation to a subdirectory installation?
	 *
	 * @param AngieModelWordpressConfiguration $config
	 * @param                                  $replacePaths
	 * @param                                  $newDomain
	 *
	 * @return  bool
	 */
	private function mustConvertSudomainsToSubdirs(AngieModelWordpressConfiguration $config, $replacePaths, $newDomain)
	{
		$useSubdomains = $config->get('subdomain_install', 0);

		// If we use subdomains and we are restoring to a different path we MUST convert subdomains to subdirectories
		$convertSubdomainsToSubdirs = $replacePaths && $useSubdomains;

		if (!$convertSubdomainsToSubdirs && $useSubdomains && ($newDomain == 'localhost'))
		{
			/**
			 * Special case: localhost
			 *
			 * Localhost DOES NOT support subdomains. Therefore the subdomain multisite installation MUST be converted
			 * to a subdirectory installation.
			 *
			 * Why is this special case needed? The previous line will only be triggered if we are restoring to a
			 * different path. However, when you are restoring to localhost you ARE restoring to the root of the site,
			 * i.e. the same path as a live multisite subfolder installation of WordPress. This would mean that ANGIE
			 * would try to restore as a subdomain installation which would fail on localhost.
			 */
			$convertSubdomainsToSubdirs = true;
		}

		return $convertSubdomainsToSubdirs;
	}

	/**
	 * Update the wp-config.php file. Required for multisite installations. I can't add this to the
	 * AngieModelWordpressSetup model
	 *
	 * @return  bool
	 */
	public function updateWPConfigFile(AngieModelWordpressConfiguration $config)
	{
		// Update the base directory, if present
		$base = $config->get('base', null);

		if (!is_null($base))
		{
			$base = '/' . trim($config->getNewBasePath(), '/');
			$config->set('base', $base);
		}

		// If I have to convert subdomains to subdirs then I need to update SUBDOMAIN_INSTALL as well
		$old_url = $config->get('oldurl');
		$new_url = $config->get('homeurl');

		$oldUri = new AUri($old_url);
		$newUri = new AUri($new_url);

		$newDomain = $newUri->getHost();

		$newPath = $newUri->getPath();
		$newPath = empty($newPath) ? '/' : $newPath;
		$oldPath = $config->get('path_current_site', $oldUri->getPath());

		$replacePaths   = $oldPath != $newPath;

		$mustConvertSubdomains = $this->mustConvertSudomainsToSubdirs($config, $replacePaths, $newDomain);

		if ($mustConvertSubdomains)
		{
			$config->set('subdomain_install', 0);
		}

		// Get the wp-config.php file and try to save it
		if (!$config->writeConfig(APATH_SITE . '/wp-config.php'))
		{
			return false;
		}

		return true;
	}
}
