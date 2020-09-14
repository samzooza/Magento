<?php

namespace Project\Extensions\Controller\Adminhtml\Order;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;

use Project\Extensions\Model\Scg;

/**
 * Class MassPrintShippingLabel
 */
class MassPrintShippingLabel extends \Magento\Sales\Controller\Adminhtml\Order\AbstractMassAction
{
    protected $fileFactory;
    protected $filesystem;
    protected $scg;
    private $reAuthenFlag;
    private $token = '';
    
    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param FileFactory $fileFactory
     * @param Filesystem $filesystem
     * @param Scg $scg
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        FileFactory $fileFactory,
        Filesystem $filesystem,
        Scg $scg
    ) {
        parent::__construct($context, $filter);
        $this->collectionFactory = $collectionFactory;
        $this->fileFactory = $fileFactory;
        $this->filesystem = $filesystem;
        $this->scg = $scg;
    }

    /**
     * Hold selected orders
     *
     * @param AbstractCollection $collection
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    protected function massAction(AbstractCollection $collection)
    {
        // authentication
        $this->reAuthenFlag = false;
        $this->token = $this->Authentication();

        $trackingNumbers = array();

        foreach ($collection->getItems() as $order)
        {   // push all selected tracking numbers into array
            $tracksCollection = $order->getTracksCollection();

            foreach ($tracksCollection->getItems() as $track)
                if($track->getTrackNumber()!='')
                    array_push($trackingNumbers, $track->getTrackNumber());
        }
        
        // Print shipping labels
        $response = $this->scg->GetMobileLabel(
            $this->token,
            join(",", $trackingNumbers));

        // Save file into temp
        $filename = $this->SaveFile($response);

        return $this->fileFactory->create('shipping_labels.pdf', [
            'type' => 'filename',
            'value' => $filename,
            'rm' => true,
        ]);
    }

    protected function Authentication()
    {   // able to authen only token is empty
        if($this->token == '')
        {
            $response = json_decode($this->scg->Authentication(), true);

            if(!$response['status'])
            {   // fail, return error
                $this->messageManager->addError(__($response['message']));
                return $this->Refresh();
            }
            else // successful
                return $response['token'];
        }
        else // return existing one
            return $this->token;
    }

    public function SaveFile($response)
    {
        $directory = $this->filesystem->getDirectoryWrite(DirectoryList::TMP);
        $destination = $directory->getAbsolutePath(sprintf('export-%s.pdf', date('Ymd-His')));

        stream_copy_to_stream(fopen($response,"r"), fopen($destination, "w"));

        return $destination;
    }
}