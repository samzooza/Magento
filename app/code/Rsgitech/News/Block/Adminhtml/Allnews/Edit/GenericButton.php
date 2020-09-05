<?php

namespace Rsgitech\News\Block\Adminhtml\Allnews\Edit;

use Magento\Backend\Block\Widget\Context;
use Rsgitech\News\Api\AllnewsRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class GenericButton
{
    protected $context;
   
    protected $allnewsRepository;
    
    public function __construct(
        Context $context,
        AllnewsRepositoryInterface $allnewsRepository
    ) {
        $this->context = $context;
        $this->allnewsRepository = $allnewsRepository;
    }

    public function getNewsId()
    {
        try {
            return $this->allnewsRepository->getById(
                $this->context->getRequest()->getParam('news_id')
            )->getId();
        }
		catch (NoSuchEntityException $e) {
        
		}
        return null;
    }

    public function getUrl($route = '', $params = [])
    {
        return $this->context->getUrlBuilder()->getUrl($route, $params);
    }
}
