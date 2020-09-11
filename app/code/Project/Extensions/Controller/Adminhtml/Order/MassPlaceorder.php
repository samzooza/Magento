<?php

namespace Project\Extensions\Controller\Adminhtml\Order;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Sales\Api\OrderManagementInterface;

use Project\Extensions\Model\Scg;
use Zend\Http\Client;

/**
 * Class MassPlaceorder
 */
class MassPlaceorder extends \Magento\Sales\Controller\Adminhtml\Order\AbstractMassAction
{
    /**
     * @var OrderManagementInterface
     * @var Scg
     */
    protected $orderManagement;
    protected $scg;
    private $token = '';
    private $reAuthenFlag = false;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param OrderManagementInterface $orderManagement
     * @param Scg $scg
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        OrderManagementInterface $orderManagement,
        \Project\Extensions\Model\Scg $scg
    ) {
        parent::__construct($context, $filter);
        $this->collectionFactory = $collectionFactory;
        $this->orderManagement = $orderManagement;
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
        $this->token = $this->Authentication();

        $response = $this->PlaceOrder();

        $this->Shipping($response['trackingNumber']);

        return $this->Refresh();
    }

    protected function Authentication()
    {   // able to authen only token is empty
        if($this->token == '')
        {   // authentication
            /*  100 - Successful authentication.
                200 - Missing mandatory variable, 'username'.
                200 - Missing mandatory variable, 'password'.
                300 - Authentication failed, username is not valid.
                300 - Authentication failed, username and password combination is not correct.
                900 - Service is temporary unavailable, please try again later.
            */
            $response = $this->scg->Authentication();

            if($response['status'] != '100')
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

    protected function PlaceOrder()
    {   // place order
        /*  100 - Successful place order.
            200 - Missing mandatory variable, 'token'.
            200 - Missing mandatory variable, 'shipper_code'.
            300 - Authentication failed, token is not valid.
            400 - Variable validation / filter failed, 'shipper_code' is not valid.
            600 - Business rule validation failed, you are not authorize to place order as this shipper_code
            900 - Service is temporary unavailable, please try again later.
        */
        $response = $this->scg->PlaceOrder(
            $this->token,
            '00214110143',
            'SAM TEST SCG Landscape',
            '028888888',
            'Bankok',
            '20000',
            'Chaingmai', //$shippingaddress->getStreet().' '.$shippingaddress->getCity().' '.$shippingaddress->getPostcode(),
            '20000', //$shippingaddress->getPostcode(),
            'Natthapon Jampasri', //$orders->getCustomerFirstname().' '.$orders->getCustomerLastname(),
            '12314123', //$shippingaddress->getTelephone(),
            'ORD985631541', //$order->getEntityId(),
            '1',
            '2020-9-10'); //date("Y-m-d")

        if($response['status'] == '300' && !$this->reAuthenFlag)
        {   // existing token might be expired, try to re-authenticate it  
            $this->reAuthenFlag = true;
            $this->token = '';
            $this->Authentication();
            return $this->PlaceOrder();
        }
        else if($response['status'] != '100' && $this->reAuthenFlag)
        {   // still unsuccess, return error
            $this->messageManager->addError(__($response['message']));
            return $this->Refresh();
        }
        else
            return $response;
    }

    protected function Shipping($trackingNumber): void
    {
        $model = $this->_objectManager->create('Magento\Sales\Model\Order');        
        foreach ($collection->getItems() as $order) {
            // Check if order has already shipped or can be shipped
            if (! $order->canShip()) {
                $this->messageManager->addError(__('ID '.$order->getEntityId().': You can\'t create an shipment.'));
                continue;
            }

            // Initialize the order shipment object
            $convertOrder = $this->_objectManager->create('Magento\Sales\Model\Convert\Order');
            $shipment = $convertOrder->toShipment($order);

            // Loop through order items
            foreach ($order->getAllItems() AS $orderItem) {
                // Check if order item is virtual or has quantity to ship
                if (! $orderItem->getQtyToShip() || $orderItem->getIsVirtual()) {
                    continue;
                }

                $qtyShipped = $orderItem->getQtyToShip();

                // Create shipment item with qty
                $shipmentItem = $convertOrder->itemToShipmentItem($orderItem)->setQty($qtyShipped);

                // Add shipment item to shipment
                $shipment->addItem($shipmentItem);
            }

            // Register shipment
            $shipment->register();

            $data = array(
                'carrier_code' => 'Custom Value',
                'title' => 'SCG Express',
                'number' => $trackingNumber, // Replace with SCG tracking number
            );

            $shipment->getOrder()->setIsInProcess(true);

            try {
                // Save created shipment and order
                $track = $this->_objectManager->create('Magento\Sales\Model\Order\Shipment\TrackFactory')->create()->addData($data);
                $shipment->addTrack($track)->save();
                $shipment->save();
                $shipment->getOrder()->save();

                // // Send email
                // $this->_objectManager->create('Magento\Shipping\Model\ShipmentNotifier')
                // ->notify($shipment);

                $shipment->save();
            } catch (\Exception $e) {
                $this->messageManager->addError(__($e->getMessage()));
            }
        }
    }

    protected function Refresh(){
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath($this->getComponentRefererUrl());
        return $resultRedirect;
    } 
}