<?php
namespace Magetop\Helloworld\Controller\Adminhtml;
 
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magetop\Helloworld\Model\PostsFactory;
 
class Posts extends Action
{
    protected $_coreRegistry;
    protected $_resultPageFactory;
    protected $_postsFactory;
 
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        PageFactory $resultPageFactory,
        PostsFactory $postsFactory
    ) {
        parent::__construct($context);
        $this->_coreRegistry = $coreRegistry;
        $this->_resultPageFactory = $resultPageFactory;
        $this->_postsFactory = $postsFactory;
 
    }
    public function execute()
    {
 
    }
 
    protected function _isAllowed()
    {
        return true;
    }
}