# Zend_Cache_Backend_Memcached_Namespaces

Extends `Zend_Cache_Backend_Memcached` to allow for clearing of specific variables based
on their specified "namespace".

## Usage

This simply adds a `clear` method to `Zend_Cache_Backend_Memcached`:

    public function clear($namespace = null) { ... }

### `application.ini`:

    resources.cachemanager.friends.frontend.name = "Class"
    resources.cachemanager.friends.frontend.options.cached_entity = "\My\Model\Friends"
    resources.cachemanager.friends.frontend.options.lifetime = 3600
    resources.cachemanager.friends.frontend.options.automatic_serialization = true
    resources.cachemanager.friends.backend.name = "Memcached_Namespaced"
    
### `My\Model\Friends.php`:

    class Friends
    {
        
        ...
        
        public static function findFriendsForUser(User $user)
        {
            $friends = ... some really long process ...
            
            return $friends;
        }
        
        ...
        
    }

### `UserController.php`

    class Zend_Controller_UserController extends Zend_Controller_Action
    {
        
        protected $friendsCache;
        
        public function init()
        {
            // Store the friends cache locally for easier access
            $bootstrap = $this->getInvokeArg('bootstrap');
            $cacheManager = $bootstrap->getResource('cachemanager');
            
            $this->friendsCache = $cacheManager->getCache('friends');
            
            ...
        }
        
        public function indexAction()
        {
            // Friends list is cached for 1 hour after initial request
            $this->view->friends = $this->friendsCache->findFriendsForUser($this->view->user);
        }
        
        public function addFriendAction()
        {
            ...
            
            $this->view->user->addFriend( Friends::findById($id) );
            
            // Clear the friends cache so the changes are reflected
            $this->friendsCache->clear();
        }
        
    }
    