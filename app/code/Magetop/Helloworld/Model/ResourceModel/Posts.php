<?php
namespace Magetop\Helloworld\Model\ResourceModel;
 
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
 
class Posts extends AbstractDb
{
    protected function _construct()
    {
        // magetop_blog is table name and id is Primary of Table
        $this->_init('magetop_blog', 'id');
    }
}