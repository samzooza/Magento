<?php
namespace Magetop\Helloworld\Controller\Index;
 
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
 
class TextEvent extends \Magento\Framework\App\Action\Action
{
    protected $session;
    protected $resultPageFactory;
 
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Customer\Model\Session $session
    )
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->session = $session;
    }
 
    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $text = 'Hello ';
        $this->session->setTextMessage($text);
        $this->_eventManager->dispatch('magetop_hello_display_text_before', ['hello_message' => $text]);
        echo $this->session->getTextMessage();
    }
}