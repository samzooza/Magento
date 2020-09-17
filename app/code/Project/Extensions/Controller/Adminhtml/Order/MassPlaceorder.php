<?php

namespace Project\Extensions\Controller\Adminhtml\Order;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Customer\Model\Customer;

use Project\Extensions\Model\Scg;

/**
 * Class MassPlaceorder
 */
class MassPlaceorder extends \Magento\Sales\Controller\Adminhtml\Order\AbstractMassAction
{
    /**
     * @var Customer
     * @var Scg
     */
    protected $customer;
    protected $scg;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param Customer $customer
     * @param Scg $scg
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        Customer $customer,
        Scg $scg
    ) {
        parent::__construct($context, $filter);
        $this->collectionFactory = $collectionFactory;
        $this->customer = $customer;
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
        foreach ($collection->getItems() as $order)
        {   // check the order can ship out
            if (!$order->canShip()) {
                $this->messageManager->addError(__('ID '.strval($order->getEntityId()).': You can\'t create an shipment.'));
                continue;
            }

            // place order to SCG Express
            $response = $this->PlaceOrder($order);
            if(!$response['status'])
            {
                $this->messageManager->addError(__('ID '.strval($order->getEntityId()).': '.$response['message']));
                continue;
            }

            $this->Ship($order, $response['trackingNumber']);
            $this->messageManager->addSuccess(__('ID '.strval($order->getEntityId()).': Process successful.'));
        }

        return $this->Refresh();
    }

    protected function PlaceOrder($order)
    {
        $shippingAddress = $order->getShippingAddress();

        return $this->scg->PlaceOrder(
            '00214110143',
            'Siam Agri Supply Co., Ltd.',
            '0218565989',
            '386 Srinakarin Road, Nongbon, Prawet, Bangkok 10250, Thailand',
            '10250',
            $shippingAddress->getData("street").' '.$shippingAddress->getData("city"),
            $shippingAddress->getData("postcode"),
            $shippingAddress->getData("firstname").' '.$shippingAddress->getData("lastname"),
            $shippingAddress->getData("telephone"),
            $order->getEntityId(),
            '1',
            date("Y-m-d"));
    }

    protected function Ship($order, $trackingNumerObj): void
    {
        // initialize the order shipment object
        $convertOrder = $this->_objectManager->create('Magento\Sales\Model\Convert\Order');
        $shipment = $convertOrder->toShipment($order);

        // loop through order items
        foreach ($order->getAllItems() AS $orderItem) {
            // check if order item is virtual or has quantity to ship
            if (! $orderItem->getQtyToShip() || $orderItem->getIsVirtual()) {
                continue;
            }

            $qtyShipped = $orderItem->getQtyToShip();

            // create shipment item with qty
            $shipmentItem = $convertOrder->itemToShipmentItem($orderItem)->setQty($qtyShipped);

            // add shipment item to shipment
            $shipment->addItem($shipmentItem);
        }

        // register shipment
        $shipment->register();

        $trackingNumber = (gettype($trackingNumerObj)=='array')
                ? implode(" , ",$trackingNumerObj)
                : $trackingNumerObj;
        
        $data = array(
            'carrier_code' => 'Custom Value',
            'title' => 'SCG Express',
            'number' => $trackingNumber, // replace with SCG tracking number
        );

        $shipment->getOrder()->setIsInProcess(true);

        try {
            // save created shipment and order
            $track = $this->_objectManager->create('Magento\Sales\Model\Order\Shipment\TrackFactory')->create()->addData($data);
            $shipment->addTrack($track)->save();
            $shipment->save();
            $shipment->getOrder()->save();

            // send email
            // $this->_objectManager->create('Magento\Shipping\Model\ShipmentNotifier')
            // ->notify($shipment);

            $shipment->save();
        } catch (\Exception $e) {
            $this->messageManager->addError(__($e->getMessage()));
        }
    }

    protected function Refresh(){
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath($this->getComponentRefererUrl());
        return $resultRedirect;
    } 
}