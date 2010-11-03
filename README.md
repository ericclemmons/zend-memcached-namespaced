# Zend_Cache_Backend_Memcached_Namespace

Extends `Zend_Cache_Backend_Memcached` to allow for clearing of specific variables based
on their specified "namespace".

    // Vanilla Memcached
    $plain = new Zend_Cache_Backend_Memcached();
    
    // Namespaced Memcached
    $posts = new Zend_Cache_Backend_Memcached_Namespace(array(
        'namespace' => 'posts'
    ));
    
    // COMMAND                                 Memcached Data
    //
    $plain->save('Bob', 'firstName');       // ['firstName']        => 'Bob'
    
    $posts->save('Eric', 'firstName');      // ['firstName']        => 'Bob'
                                            // ['posts::firstName'] => 'Eric'
                                            // ['posts::__keys']    => array('posts::firstName')
    
    $posts->save('Clemmons', 'lastName');   // ['firstName']        => 'Bob'
                                            // ['posts::firstName'] => 'Eric'
                                            // ['posts::lastName']  => 'Clemmons'
                                            // ['posts::__keys']    => array('posts::firstName', 'posts::lastName')
    
    $posts->clean();                        // ['firstName']        => 'Bob'
    $plain->clean();                        // (everything's gone!)

## Usage

This simply modifies the `clean` functionality in `Zend_Cache_Backend_Memcached`
to only clean the current namespace, unless you pass in `Zend_Cache::CLEANING_MODE_ALL`.

### application.ini:

    resources.cachemanager.posts.frontend.name = "Class"
    resources.cachemanager.posts.frontend.options.cached_entity = "Posts"
    
    ; Because of "sanitization" of backends, we have to use a custom one
    resources.cachemanager.posts.backend.customBackendNaming = true
    resources.cachemanager.posts.backend.name = "Zend_Cache_Backend_Memcached_Namespace"
    
    ; This backend will be stored with keys like: "posts::$KEY"
    resources.cachemanager.posts.backend.options.namespace = 'posts'

### index.php

Clone this repo into your `vendors` directory and add it to your include path:

    set_include_path(implode(PATH_SEPARATOR, array(
        ...
        realpath(APPLICATION_PATH . '/../vendors/zend-memcached-namespaced'),
        ...
    );

### Example

    class Zend_Controller_PostsController extends Zend_Controller_Action
    {
        ...
        
        public function addPost()
        {
            $post = new Post();
            ...
            Posts::add($post);
            
            // Clean the posts cache
            $this->cache->clean();
            
            // Clean all data
            $this->cache->clean(Zend_Cache::CLEANING_MODE_ALL);
        }
        
    }

