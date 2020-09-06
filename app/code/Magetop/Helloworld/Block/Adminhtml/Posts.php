<?php
namespace Magetop\Helloworld\Block\Adminhtml;
 
use Magento\Backend\Block\Widget\Grid\Container;
 
class Posts extends Container
{
    protected function _construct()
    {
        $this->_controller = 'adminhtml_posts';
        $this->_blockGroup = 'Magetop_Helloworld';
        $this->_headerText = __('Manage Posts');
        $this->_addButtonLabel = __('Add New Post');
        parent::_construct();
    }
}