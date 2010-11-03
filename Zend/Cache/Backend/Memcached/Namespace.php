<?php

class Zend_Cache_Backend_Memcached_Namespace extends Zend_Cache_Backend_Memcached
{
    /*
     * These are the same options as Memcached, except with a "namespace"
     * @var array available options
     */
    protected $_options = array(
        'servers' => array(array(
            'host' => self::DEFAULT_HOST,
            'port' => self::DEFAULT_PORT,
            'persistent' => self::DEFAULT_PERSISTENT,
            'weight'  => self::DEFAULT_WEIGHT,
            'timeout' => self::DEFAULT_TIMEOUT,
            'retry_interval' => self::DEFAULT_RETRY_INTERVAL,
            'status' => self::DEFAULT_STATUS,
            'failure_callback' => self::DEFAULT_FAILURE_CALLBACK
        )),
        'compression' => false,
        'compatibility' => false,
        'namespace' => null,
        'namespace_delimiter' => '::'
    );
    
    /**
     * This ID (when prefixed with the namespace) keeps track of which IDs
     * are associated with a single namespace
     */
    protected $_trackingId = '__keys';
    
    /**
     * Cleans only the current namespace, by default
     *
     * Available modes are :
     * 'all' (default)  => remove all cache entries ($tags is not used)
     * 'old'            => unsupported
     * 'matchingTag'    => unsupported
     * 'notMatchingTag' => unsupported
     * 'matchingAnyTag' => unsupported
     *
     * @param  string $mode Clean mode
     * @param  array  $tags Array of tags
     * @throws Zend_Cache_Exception
     * @return boolean True if no problem
     */
    public function clean($mode = null, $tags = array())
    {
        // Honor parent's cleaning functionality
        if ($mode) {
            parent::clean($mode, $tags);
        } else {
        // Default to only removing what's in the current namespace
            foreach ($this->getTrackedIds() as $id) {
                parent::remove($id);
            }
            
            parent::remove($this->getNamespacedId( $this->_trackingId) );
        }
    }
    
    /**
     * @var string ID
     * @return string Namespaced ID
     */
    public function getNamespacedId($id)
    {
        // Allow for a string or array of namespaces
        $namespaces = (Array) $this->_options['namespace'];
        
        // Append the specified ID
        $namespaces[] = $id;
        
        $delimiter = $this->_options['namespace_delimiter'];
        
        $id = join($delimiter, $namespaces);
        
        return $id;
    }
    
    /**
     * 
     *
     * @return array IDs associated with current namespace
     */
    public function getTrackedIds()
    {
        $ids = parent::load( $this->getNamespacedId( $this->_trackingId ) )
            ?: array();
        
        return $ids;
    }
    
    /**
     * Test if a cache is available for the given id and (if yes) return it (false else)
     *
     * @param  string  $id                     Cache id
     * @param  boolean $doNotTestCacheValidity If set to true, the cache validity won't be tested
     * @return string|false cached datas
     */
    public function load($id, $doNotTestCacheValidity = false)
    {
        return parent::load( $this->getNamespacedId( $id ), $doNotTestCacheValidity);
    }
    
    /**
     * Save some string datas into a cache record
     *
     * Note : $data is always "string" (serialization is done by the
     * core not by the backend)
     *
     * @param  string $data             Datas to cache
     * @param  string $id               Cache id
     * @param  array  $tags             Array of strings, the cache record will be tagged by each string entry
     * @param  int    $specificLifetime If != false, set a specific lifetime for this cache record (null => infinite lifetime)
     * @return boolean True if no problem
     */
    public function save($data, $id, $tags = array(), $specificLifetime = false)
    {
        $id = $this->getNamespacedId($id);
        $trackingId = $this->getNamespacedId($this->_trackingId);
        
        $ids = parent::load($trackingId) ?: array();
        $ids[] = $id;
        
        parent::save($ids, $trackingId);
        
        return parent::save($data, $id, $tags, $specificLifetime);
    }
    
}