<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Plugin;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Zend\Session\Container;
use Zend\Session\ManagerInterface as Manager;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;

/**
 * Flash Messenger - implement session-based messages
 */
class ChatBox extends AbstractPlugin implements IteratorAggregate, Countable
{
    /**
     * @var Container
     */
    protected $container;

    /**
     * Chat box open or hidden
     * @var array
     */
    protected $chatboxes = array();

    /**
     * @var Manager
     */
    protected $session;
 

    /**
     * Set the session manager
     *
     * @param  Manager        $manager
     * @return ChatBox
     */
    public function setSessionManager(Manager $manager)
    {
        $this->session = $manager;

        return $this;
    }

    /**
     * Retrieve the session manager
     *
     * If none composed, lazy-loads a SessionManager instance
     *
     * @return Manager
     */
    public function getSessionManager()
    {
        if (!$this->session instanceof Manager) {
            $this->setSessionManager(Container::getDefaultManager());
        }

        return $this->session;
    }

    /**
     * Get session container for flash messages
     *
     * @return Container
     */
    public function getContainer()
    {
        if ($this->container instanceof Container) {
            return $this->container;
        }

        $manager = $this->getSessionManager();
        $this->container = new Container('ChatBoxes', $manager);

        return $this->container->item;
    }

    /**
     * Get messages from a specific namespace
     *
     * @return array
     */
    public function getChatboxes()
    {
        if ($this->hasChatBoxes()) {
            return $this->chatboxes;
        }

        return array();
    }

    /**
     * Complete the IteratorAggregate interface, for iterating
     *
     * @return ArrayIterator
     */
    public function getIterator()
    {
        if ($this->hasChatBoxes()) {
            return new ArrayIterator($this->getChatboxes());
        }

        return new ArrayIterator();
    }

    /**
     * Complete the countable interface
     *
     * @return int
     */
    public function count()
    {
        if ($this->hasChatBoxes()) {
            return count($this->getChatboxes());
        }

        return 0;
    }
	
    /**
     * Whether a specific namespace has messages
     *
     * @return bool
     */
    public function hasChatBoxes()
    {
    	$this->getChatBoxesFromContainer();
    
    	return isset($this->chatboxes);
    }

    /**
     * Pull messages from the session container
     *
     * Iterates through the session container, removing messages into the local
     * scope.
     *
     * @return void
     */
    protected function getChatboxesFromContainer()
    {
        if (!empty($this->chatboxes)) {
            return;
        }

        $this->chatboxes = $this->getContainer();
    }
}
