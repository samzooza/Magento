<?php
namespace Magetop\Helloworld\Block;
 
use Magento\Framework\View\Element\Template;
use Magento\Framework\Registry;
 
class Posts extends Template
{
    protected $_coreRegistry;
 
    protected $_postsFactory;
 
    public function __construct(
        Template\Context $context,
        Registry $coreRegistry,
        \Magetop\Helloworld\Model\ResourceModel\Posts\CollectionFactory $postsFactory,
        array $data = []
    )
    {
 
        parent::__construct($context, $data);
        $this->_coreRegistry = $coreRegistry;
        $this->_postsFactory = $postsFactory;
    }
 
    /**
     * @return $this|mixed
     */
    function getPostItems() {
        if($this->_coreRegistry->registry('post_items'))
        {
            $collection = $this->_coreRegistry->registry('post_items');
        }
        else {
            $collection = $this->_postsFactory->create()
                ->addFieldToSelect(array('title','description'))
                ->addFieldToFilter('status',1)
                ->setPageSize(10)
                ->setOrder('id','ASC');
            $this->_coreRegistry->register('post_items',$collection);
        }
        return $collection;
 
    }
 
}