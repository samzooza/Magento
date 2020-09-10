<?php

namespace Api\Scg\Controller\Adminhtml\Order;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Sales\Api\OrderManagementInterface;
use Zend\Http\Client;

/**
 * Class MassPlaceorder
 */
class MassPlaceorder extends \Magento\Sales\Controller\Adminhtml\Order\AbstractMassAction
{
    /**
     * @var OrderManagementInterface
     */
    protected $orderManagement;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param OrderManagementInterface $orderManagement
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        OrderManagementInterface $orderManagement
    ) {
        parent::__construct($context, $filter);
        $this->collectionFactory = $collectionFactory;
        $this->orderManagement = $orderManagement;
    }

    /**
     * Hold selected orders
     *
     * @param AbstractCollection $collection
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    protected function massAction(AbstractCollection $collection)
    {
        $token = '';
        $trackingNumber = '';
        // authentication
        try 
        {
            //document: https://framework.zend.com/manual/2.4/en/modules/zend.http.client.html
            $client = new Client();
            $client->setUri('https://scgyamatodev.flare.works/api/authentication');
            $client->setOptions(array('maxredirects' => 0, 'timeout' => 30));
            $client->setParameterPost(array(
                'username' => 'info_test@scgexpress.co.th',
                'password' => 'Initial@1234'
            ));
            $client->setMethod('POST');
            
            $response = $client->send();
            if ($response->isSuccess()) {
                $obj = json_decode($response->getbody(), true);
                $token = $obj["token"];
            }
        }
        catch (\Zend\Http\Exception\RuntimeException $runtimeException) 
        {
            $this->messageManager->addError(__($runtimeException->getMessage()));
        }

        // place order
        try 
        {
            // $shippingaddress=$order->getShippingAddress()->getData();

            $client = new Client();
            $client->setUri('https://scgyamatodev.flare.works/api/orderwithouttrackingnumber');
            $client->setOptions(array('maxredirects' => 1, 'timeout' => 300));
            $client->setParameterPost(array(
                'token' => $token,
                'ShipperCode' => '00214110143',
                'ShipperName' => 'SAM TEST SCG Landscape',
                'ShipperTel' => '028888888',
                'ShipperAddress' => 'Bankok',
                'ShipperZipcode' => '20000',
                'DeliveryAddress' => 'Chaingmai', //$shippingaddress->getStreet().' '.$shippingaddress->getCity().' '.$shippingaddress->getPostcode(),
                'Zipcode' => '20000', //$shippingaddress->getPostcode(),
                'ContactName' => 'Natthapon Jampasri', //$orders->getCustomerFirstname().' '.$orders->getCustomerLastname(),
                'Tel' => '12314123', //$shippingaddress->getTelephone(),
                'OrderCode' => 'ORD985631541', //$order->getEntityId(),
                'TotalBoxs' => '1',
                'OrderDate' => '2020-9-10' //date("Y-m-d")
            ));
            $client->setMethod('POST');
            
            $response = $client->send();
            if ($response->isSuccess()) {
                $obj = json_decode($response->getbody(), true);
                $trackingNumber = $response->getbody();
            }
        }
        catch (\Zend\Http\Exception\RuntimeException $runtimeException) 
        {
           $this->messageManager->addError(__($runtimeException->getMessage()));
        }

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
                'number' => $trackingNumber, // Replace with your tracking number
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

        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath($this->getComponentRefererUrl());
        return $resultRedirect;
    }
}