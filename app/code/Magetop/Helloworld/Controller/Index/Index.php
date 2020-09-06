<?php
namespace Magetop\Helloworld\Controller\Index;
 
use Magento\Framework\App\Action\Context;
use Magetop\Helloworld\Model\ResourceModel\Posts\CollectionFactory;
use Magetop\Helloworld\Helper\Data;
 
class Index extends \Magento\Framework\App\Action\Action
{
    protected $_resultPageFactory;
    protected $_postsFactory;
    protected $_dataHelper;
 
    public function __construct(
        Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        CollectionFactory $postsFactory,
        Data $dataHelper)
    {
        parent::__construct($context);
        $this->_resultPageFactory = $resultPageFactory;
        $this->_postsFactory = $postsFactory;
        $this->_dataHelper = $dataHelper;
    }
 
    public function execute()
    {
        echo "Get Data From magetop_blog table <br>";
        $numberPosts = $this->_dataHelper->getHelloSetting('blog/setting/number_posts');
        echo "Number Posts = {$numberPosts}";
        $this->_postsFactory->create();
        $collection = $this->_postsFactory->create()
            ->addFieldToSelect(array('title','description','created_at','status')) // fields to select
            ->addFieldToFilter('status',1) // filter status = 1
            ->setPageSize($numberPosts); // get 2 items
        echo '<pre>';
        print_r($collection->getData());
        echo '<pre>';
        echo "==========Check date, helper function ======== <br>";
        $date = '2020-04-01';
        if ($this->_dataHelper->checkDate($date)) {
            echo "Yes, {$date} is Sunday , I can go to your home";
        } else {
            echo "Yes, {$date} is not Sunday , I was to busy";
        }
    }
}