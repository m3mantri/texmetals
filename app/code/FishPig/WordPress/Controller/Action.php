<?php
/**
 * @ 
**/
namespace FishPig\WordPress\Controller;

abstract class Action extends \Magento\Framework\App\Action\Action
{
	/**
	 * @var 
	**/
	protected $app = null;
	
	/**
	 * @var 
	**/
	protected $registry = null;

	/**
	 * @var 
	**/	
	protected $_entity = null;
	
	/**
	 * @var 
	**/	
	protected $factory = null;

	/**
	 * @var 
	**/	
	protected $resultPage = null;
	

	/**
	 * @var 
	**/	
	protected $entity = null;
	
	/**
	 * @var 
	**/
	abstract protected function _getEntity();

    /**
     * Constructor
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
	    \Magento\Framework\App\Action\Context $context, 
	    \Magento\Framework\Registry $registry, 
	    \FishPig\WordPress\Model\App $app,
	    \FishPig\WordPress\Model\App\Factory $factory
	   )
    {
	    
		$this->registry = $registry;
		$this->app = $app;
		$this->factory = $factory;
        	
        parent::__construct($context);
    }	

    /**
     * Load the page defined in view/frontend/layout/samplenewpage_index_index.xml
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
	    try {
			$this->_beforeExecute();
			
			if ($forward = $this->_getForward()) {
				return $forward;
			}

		    $this->_initLayout();

		    $this->_afterExecute();

		    return $this->getPage();
		}
		catch (\Exception $e) {
			echo 'Exception: ' . $e->getMessage() . '<br/><br/><pre>' . $e->getTraceAsString() . '</pre>';
			exit;
		}
    }

	protected function _getForward()
	{
		return false;
	}
	
	protected function _beforeExecute()
	{
	    if (($entity = $this->_getEntity()) === false) {
		    throw new \Exception('Entity not found!');
	    }
	    
	    if ($entity !== null) {
		    $this->registry->register($entity::ENTITY, $entity);
		}

		return $this;	
	}
	
    /**
	 * @
	**/
    protected function _initLayout()
    {
	    
		// Add blog feed URL
		// Add blog comments feed URL
		// Add canonical

	    
	    if ($handles = $this->getLayoutHandles()) {
			$handles = array_reverse($handles);

		    foreach($handles as $handle) {
				$this->getPage()->addHandle($handle);
			}
		}

	    $this->getPage()->getConfig()->addBodyClass('is-blog');
	    
		if ($breadcrumbsBlock = $this->_view->getLayout()->getBlock('breadcrumbs')) {	    
		    if ($crumbs = $this->_getBreadcrumbs()) {
			    foreach($crumbs as $key => $crumb) {
				    $breadcrumbsBlock->addCrumb($key, $crumb);
			    }
		    }
		}

	    return $this;
    }
    
    /**
	 * Get an array of extra layout handles to apply
	 *
	 * @return array
	**/
    public function getLayoutHandles()
    {
	    return ['wordpress_default'];
    }

    /**
	  * Get the breadcrumbs
	  *
	  * @return array
	 **/
    protected function _getBreadcrumbs()
    {
	    $crumbs = [
		    'home' => [
			'label' => __('Home'),
			'title' => __('Go to Home Page'),
			'link' => $this->app->getWpUrlBuilder()->getMagentoUrl()
		]];
		
		if (!$this->app->isRoot()) {
			$crumbs['blog'] = [
				'label' => __('Blog'),
				'link' => $this->app->getWpUrlBuilder()->getHomeUrl()
			];
		}
		
		return $crumbs;
    }
    
    /**
	 * @
	**/
	protected function _afterExecute()
	{
		return $this;
	}
    
	/**
	 * @var 
	**/
	public function getPage()
	{
		if ($this->resultPage === null) {
			$this->resultPage = $this->resultFactory->create(
				\Magento\Framework\Controller\ResultFactory::TYPE_PAGE
			);
		}
		
		return $this->resultPage;
	}

	/**
	 * @var 
	**/
	public function getEntityObject()
    {
	    if ($this->entity !== null) {
		    return $this->entity;
	    }

	    return $this->entity = $this->_getEntity();
    }
    
	/**
	 * @var 
	**/
    public function getRegistry()
    {
	    return $this->registry;
    }
    
	/**
	 * @var 
	**/
    protected function _getRegistry()
    {
	    return $this->getRegistry();
    }
    
	/**
	 * @var 
	**/
    public function getApp()
    {
	    return $this->app;
    }
    
	/**
	 * @var 
	**/
    public function getFactory($type)
    {
	    return $this->factory->getFactory($type);
    }
}
