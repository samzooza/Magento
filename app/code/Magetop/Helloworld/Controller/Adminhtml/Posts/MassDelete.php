<?php
namespace Magetop\Helloworld\Controller\Adminhtml\Posts;
 
use Magetop\Helloworld\Controller\Adminhtml\Posts;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magetop\Helloworld\Model\PostsFactory;
use Magetop\Helloworld\Model\ResourceModel\PostsFactory as resPostsFactory;
 
class MassDelete extends Posts
{
    protected $_resPostsFactory;
 
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        PageFactory $resultPageFactory,
        PostsFactory $postsFactory,
        resPostsFactory $resPostsFactory
    )
    {
        parent::__construct($context, $coreRegistry, $resultPageFactory, $postsFactory);
        $this->_resPostsFactory = $resPostsFactory;
    }
 
    public function execute()
    {
        $postIds = $this->getRequest()->getParam('posts', array());
        $model = $this->_postsFactory->create();
        $resModel = $this->_resPostsFactory->create();
        if(count($postIds))
        {
            $i = 0;
            foreach ($postIds as $postId) {
                try {
                    $resModel->load($model,$postId);
                    $resModel->delete($model);
                    $i++;
                } catch (\Exception $e) {
                    $this->messageManager->addErrorMessage($e->getMessage());
                }
            }
            if ($i > 0) {
                $this->messageManager->addSuccessMessage(
                    __('A total of %1 item(s) were deleted.', $i)
                );
            }
        }
        else
        {
            $this->messageManager->addErrorMessage(
                __('You can not delete item(s), Please check again %1')
            );
        }
        $this->_redirect('*/*/index');
    }
}