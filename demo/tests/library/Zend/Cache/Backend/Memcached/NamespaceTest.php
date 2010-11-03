<?php

class Zend_Cache_Backend_Memcached_NamespaceTest extends PHPUnit_Framework_TestCase
{
    
    public function setUp()
    {
        $bootstrap = Zend_Registry::get('bootstrap');
        $cacheManager = $bootstrap->getResource('cachemanager');
        
        $this->cache = $cacheManager->getCache('posts');
        $this->backend = $this->cache->getBackend();
        $this->backend->clean();
        
        // MD5 of the $cache->findAll() call
        $this->md5 = '8ec867c5280f8fdfbbdd1f22933d7492';
        $this->namespacedMd5 = 'posts::8ec867c5280f8fdfbbdd1f22933d7492';
    }
    
    public function testMemcached()
    {
        // Result should not be cached
        $this->assertFalse( $this->backend->load( $this->md5 ) );
        $this->assertEmpty( $this->backend->getTrackedIds() );
        
        $this->cache->findAll();
        
        // Result should not be FALSE, NULL or empty.
        $rs = $this->backend->load( $this->md5 );
        $this->assertFalse( empty($rs) );
    }
    
    public function testMemcachedTrackingIds()
    {
        // Result should not be cached
        $this->assertEmpty( $this->backend->getTrackedIds() );
        
        // Tracked IDs should contain only single entry
        $this->cache->findAll();
        $this->assertContains( $this->namespacedMd5, $this->backend->getTrackedIds() );
        $this->assertEquals( 1, count($this->backend->getTrackedIds()) );
        
        // Subsequent calls should still contain only that single entry
        $this->cache->findAll();
        $this->assertContains( $this->namespacedMd5, $this->backend->getTrackedIds() );
        $this->assertEquals( 1, count($this->backend->getTrackedIds()) );
    }
    
    /**
     * @depends testMemcached
     * @dataProvider plainMemcachedProvider
     */
    public function testMemcachedClean($memcached)
    {
        // Ensure the basic memcached adapter starts with a value
        $this->assertEquals( 'myValue', $memcached->load('myKey') );
        
        // Result should not be cached
        $this->assertFalse( $this->backend->load( $this->md5 ) );
        
        $this->cache->findAll();
        
        // Result should not be FALSE, NULL or empty.
        $rs = $this->backend->load( $this->md5 );
        $this->assertFalse( empty($rs) );
        
        // There should only be a single entry
        $this->assertEquals( 1, count($this->backend->getTrackedIds()) );
        
        $this->backend->clean();
        
        // Result should not be cached
        $this->assertFalse( $this->backend->load( $this->md5 ) );
        
        // There should be no tracked IDs
        $this->assertEquals( 0, count($this->backend->getTrackedIds()) );
        
        // Ensure the basic memcached adapter still has a value
        $this->assertEquals( 'myValue', $memcached->load('myKey') );
    }
    
    public function plainMemcachedProvider()
    {
        $backend = new Zend_Cache_Backend_Memcached();
        $backend->save('myValue', 'myKey');
        
        return array(
            array($backend)
        );
    }
}