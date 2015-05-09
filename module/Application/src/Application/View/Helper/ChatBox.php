<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\View\Helper;

use Application\Plugin\ChatBox as PluginChatBox;
use Zend\View\Helper\AbstractHelper;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\View\Helper\PartialLoop;
use Zend\View\Helper\EscapeHtml;
use Zend\View;

/**
 * Helper for convert date to age
 */
class ChatBox extends AbstractHelper
{
	
    /**
     * Html escape helper
     *
     * @var EscapeHtml
     */
    protected $escapeHtmlHelper;
    
    /**
     * partial loop
     *
     * @var PartialLoop
     */
    protected $partialLoopHelper;

    /**
     * Chat box plugin
     *
     * @var PluginChatBox
     */
    protected $pluginChatBox;

    /**
     * Service locator
     *
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator;

    /**
     * Returns the chat box plugin controller
     *
     * @param  string|null $namespace
     * @return ChatBox|PluginChatBox
     */
    public function __invoke()
    {
    	return $this;
    }

    /**
     * Proxy the flash messenger plugin controller
     *
     * @param  string $method
     * @param  array  $argv
     * @return mixed
     */
    public function __call($method, $argv)
    {
        $chatBox = $this->getPluginChatBox();
        return call_user_func_array(array($chatBox, $method), $argv);
    }

    /**
     * Render Messages
     *
     * @return string
     */
    public function render()
    {
        $chatBox = $this->getPluginChatBox();
        $chatboxes = $chatBox->getChatboxes();
        
        $partialLooper = $this->view->plugin('partialLoop');
        
        return $partialLooper('partial/chatbox.phtml',$chatboxes);
        
    }
    
    /**
     * Set the chat box plugin
     *
     * @param  PluginChatBox $pluginChatBox
     * @return ChatBox
     */
    public function setPluginChatBox(PluginChatBox $pluginChatBox)
    {
        $this->pluginChatBox = $pluginChatBox;
        return $this;
    }

    /**
     * Get the chat box plugin
     *
     * @return PluginChatBox
     */
    public function getPluginChatBox()
    {
        if (null === $this->pluginChatBox) {
            $this->setPluginChatBox(new PluginChatBox());
        }

        return $this->pluginChatBox;
    }
    
    /**
     * Retrieve the escapeHtml helper
     *
     * @return EscapeHtml
     */
    protected function getEscapeHtmlHelper()
    {
    	if ($this->escapeHtmlHelper) {
    		return $this->escapeHtmlHelper;
    	}
    
    	if (method_exists($this->getView(), 'plugin')) {
    		$this->escapeHtmlHelper = $this->view->plugin('escapehtml');
    	}
    
    	if (!$this->escapeHtmlHelper instanceof EscapeHtml) {
    		$this->escapeHtmlHelper = new EscapeHtml();
    	}
    
    	return $this->escapeHtmlHelper;
    }
    
    /**
     * Retrieve the partialLoop helper
     *
     * @return PartialLoop
     */
    protected function getPartialLoopHelper()
    {
    	if ($this->partialLoopHelper) {
    		return $this->partialLoopHelper;
    	}
    
    	if (method_exists($this->getView(), 'plugin')) {
    		$this->partialLoopHelper = $this->view->plugin('partialLoop');
    	}
    
    	if (!$this->escapeHtmlHelper instanceof PartialLoop) {
    		$this->escapeHtmlHelper = new PartialLoop();
    	}
    
    	return $this->escapeHtmlHelper;
    }
}
