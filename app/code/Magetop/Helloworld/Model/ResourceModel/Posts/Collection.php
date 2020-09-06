<?php
namespace Magetop\Helloworld\Model\ResourceModel\Posts;
 
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
 
class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            'Magetop\Helloworld\Model\Posts',
            'Magetop\Helloworld\Model\ResourceModel\Posts'
        );
    }
}