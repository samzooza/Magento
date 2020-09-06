<?php
namespace Magetop\Helloworld\Controller\Adminhtml\Posts;
 
use Magetop\Helloworld\Controller\Adminhtml\Posts;
 
class Grid extends Posts
{
    public function execute()
    {
        return $this->_resultPageFactory->create();
    }
}