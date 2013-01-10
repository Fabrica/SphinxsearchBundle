<?php

namespace Delocker\SphinxsearchBundle\Services\Search;

class Sphinxsearch
{
	/**
	 * @var string $host
	 */
	private $host;

	/**
	 * @var string $port
	 */
	private $port;

	/**
	 * @var string $socket
	 */
	private $socket;

	/**
	 * @var array $indexes
	 *
	 * $this->indexes should look like:
	 *
	 * $this->indexes = array(
	 *   'IndexLabel' => array("index_name" => 'Index name as defined in sphinx.conf'),
	 *   ...,
	 * );
	 */
	private $indexes;

	/**
	 * @var \SphinxClient $sphinx
	 */
	private $sphinx;

	/**
	 * Constructor.
	 *
	 * @param string $host The server's host name/IP.
	 * @param string $port The port that the server is listening on.
	 * @param string $socket The UNIX socket that the server is listening on.
	 * @param array $indexes The list of indexes that can be used.
	 */
	public function __construct($host = 'localhost', $port = '9312', $socket = null, array $indexes = array())
	{
		$this->host = $host;
		$this->port = $port;
		$this->socket = $socket;
		$this->indexes = $indexes;

		$this->sphinx = new \SphinxClient();
		if( $this->socket !== null )
			$this->sphinx->setServer($this->socket);
		else
			$this->sphinx->setServer($this->host, $this->port);
	}

	/**
	 * Escape the supplied string.
	 *
	 * @param string $string The string to be escaped.
	 *
	 * @return string The escaped string.
	 */
	public function escapeString($string)
	{
		return $this->sphinx->escapeString($string);
	}

    /**
     * Set group by
     * @param $attribute
     * @param $func
     * @param null $groupsort
     */
    public function setGroupBy($attribute, $func, $groupsort = null)
    {
        $this->sphinx->SetGroupBy($attribute, $func, $groupsort);
    }

	/**
	 * Set the desired match mode.
	 *
	 * @param int $mode The matching mode to be used.
	 */
	public function setMatchMode($mode)
	{
		$this->sphinx->setMatchMode($mode);
	}

    /**
     * Set the desired sort mode
     * @param $mode
     * @param $str
     */
    public function setSortMode($mode, $str) {
        $this->sphinx->setSortMode($mode, $str);
    }

    /**
     * Set filter float range
     * @param $attribute
     * @param $min
     * @param $max
     * @param bool $exclude
     */
    public function setFilterFloatRange($attribute, $min, $max, $exclude = false) {
        $this->sphinx->SetFilterFloatRange($attribute, $min, $max, $exclude);
    }

    /**
     * Set geo
     * @param $attrlat
     * @param $attrlong
     * @param $lat
     * @param $long
     */
    public function setGeoAnchor($attrlat, $attrlong, $lat, $long) {
        $this->sphinx->SetGeoAnchor($attrlat, $attrlong, $lat, $long);
    }

	/**
	 * Set the desired search filter.
	 *
	 * @param string $attribute The attribute to filter.
	 * @param array $values The values to filter.
	 * @param bool $exclude Is this an exclusion filter?
	 */
	public function setFilter($attribute, $values, $exclude = false)
	{
		$this->sphinx->setFilter($attribute, $values, $exclude);
	}

	/**
	 * Search for the specified query string.
	 *
	 * @param string $query The query string that we are searching for.
	 * @param array $indexes The indexes to perform the search on.
	 *
	 * @return array The results of the search.
	 *
	 * $indexes should look like:
	 *
	 * $indexes = array(
	 *   'IndexLabel' => array(
	 *     'result_offset' => (int), // optional unless result_limit is set
	 *     'result_limit'  => (int), // optional unless result_offset is set
	 *     'field_weights' => array( // optional
	 *       'FieldName'   => (int),
	 *       ...,
	 *     ),
	 *   ),
	 *   ...,
	 * );
	 */
	public function search($query, array $indexes, $escapeQuery = true)
	{
		if( $escapeQuery )
			$query = $this->sphinx->escapeString($query);

		$results = array();
		foreach( $indexes as $label => $options ) {
			/**
			 * Ensure that the label corresponds to a defined index.
			 */
			if( !isset($this->indexes[$label]) )
				continue;

			/**
			 * Set the offset and limit for the returned results.
			 */
			if( isset($options['result_limit']) )
            {
                if ( !isset($options['result_offset']) )
                {
                    $options['result_offset'] = 0;
                }
				$this->sphinx->setLimits($options['result_offset'], $options['result_limit']);
            }

			/**
			 * Weight the individual fields.
			 */
			if( isset($options['field_weights']) )
				$this->sphinx->setFieldWeights($options['field_weights']);

			/**
			 * Perform the query.
			 */
			$results[$label] = $this->sphinx->query($query, $label);
			if( $results[$label]['status'] !== SEARCHD_OK )
				throw new \RuntimeException(sprintf('Searching index "%s" for "%s" failed with error "%s".', $label, $query, $this->sphinx->getLastError()));
		}

		/**
		 * If only one index was searched, return that index's results directly.
		 */
		if( count($indexes) === 1 && count($results) === 1 )
			$results = reset($results);

		/**
		 * FIXME: Throw an exception if $results is empty?
		 */
		return $results;
	}

    /**
     * @return \SphinxClient
     */
    public function getSphinx()
    {
        return $this->sphinx;
    }
}
