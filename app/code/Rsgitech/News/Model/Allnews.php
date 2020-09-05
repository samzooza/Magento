<?php
namespace Rsgitech\News\Model;

use Rsgitech\News\Api\Data\AllnewsInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;

class Allnews extends AbstractModel implements AllnewsInterface, IdentityInterface
{
	const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;
	
	const CACHE_TAG = 'rsgitech_news';
	
	//Unique identifier for use within caching
	protected $_cacheTag = self::CACHE_TAG;
	
	protected function _construct()
    {
        $this->_init('Rsgitech\News\Model\ResourceModel\Allnews');
    }
	
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    public function getDefaultValues()
    {
        $values = [];
        return $values;
    }
	
	public function getAvailableStatuses()
    {
        return [self::STATUS_ENABLED => __('Enabled'), self::STATUS_DISABLED => __('Disabled')];
    }
	
	public function getId()
    {
        return parent::getData(self::NEWS_ID);
    }
	
	public function getTitle()
    {
        return $this->getData(self::TITLE);
    }
	
	public function getDescription()
    {
        return $this->getData(self::DESCRIPTION);
    }
	
	public function getStatus()
    {
        return $this->getData(self::STATUS);
    }
	
	public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }
	
	public function getUpdatedAt()
    {
        return $this->getData(self::UPDATED_AT);
    }
	
	public function setId($id)
    {
        return $this->setData(self::NEWS_ID, $id);
    }
	
	public function setTitle($title)
    {
        return $this->setData(self::TITLE, $title);
    }
	
	public function setDescription($description)
    {
        return $this->setData(self::DESCRIPTION, $description);
    }
	
	public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }
	
	public function setCreatedAt($created_at)
    {
        return $this->setData(self::CREATED_AT, $created_at);
    }
	
	public function setUpdatedAt($updated_at)
    {
        return $this->setData(self::UPDATED_AT, $updated_at);
    }
}