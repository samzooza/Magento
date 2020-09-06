<?php
namespace Magetop\Helloworld\Controller\Index;
 
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
 
class Lists extends \Magento\Framework\App\Action\Action
{
    protected $_resultPageFactory;
 
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    )
    {
        parent::__construct($context);
        $this->_resultPageFactory = $resultPageFactory;
 
    }
 
    public function execute()
    {
        $resultPageFactory = $this->_resultPageFactory->create();
 
        // Add page title
        $resultPageFactory->getConfig()->getTitle()->set(__('Posts List'));
 
        // Add breadcrumb
        /** @var \Magento\Theme\Block\Html\Breadcrumbs */
        if($resultPageFactory->getLayout()->getBlock('breadcrumbs'))
        {
            $breadcrumbs = $resultPageFactory->getLayout()->getBlock('breadcrumbs');
            $breadcrumbs->addCrumb('home',
                [
                    'label' => __('Home'),
                    'title' => __('Home'),
                    'link' => $this->_url->getUrl('')
                ]
            );
            $breadcrumbs->addCrumb('booking_search',
                [
                    'label' => __('Posts'),
                    'title' => __('Posts')
                ]
            );
        }
        return $resultPageFactory;
    }
}