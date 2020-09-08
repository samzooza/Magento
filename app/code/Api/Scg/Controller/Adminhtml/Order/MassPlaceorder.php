<?php

namespace Api\Scg\Controller\Adminhtml\Order;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Sales\Api\OrderManagementInterface;

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
        // $countDeleteOrder = 0;
        // $model = $this->_objectManager->create('Magento\Sales\Model\Order');
        // foreach ($collection->getItems() as $order) {
        //     if (!$order->getEntityId()) {
        //         continue;
        //     }
        //     $loadedOrder = $model->load($order->getEntityId());
        //     $loadedOrder->delete();
        //     $countDeleteOrder++;
        // }
        // $countNonDeleteOrder = $collection->count() - $countDeleteOrder;

        // if ($countNonDeleteOrder && $countDeleteOrder) {
        //     $this->messageManager->addError(__('%1 order(s) were not deleted.', $countNonDeleteOrder));
        // } elseif ($countNonDeleteOrder) {
        //     $this->messageManager->addError(__('No order(s) were deleted.'));
        // }

        // if ($countDeleteOrder) {
        //     $this->messageManager->addSuccess(__('You have deleted %1 order(s).', $countDeleteOrder));
        // }

        $model = $this->_objectManager->create('Magento\Sales\Model\Order');        
        foreach ($collection->getItems() as $order) {
            // // Check if order has already shipped or can be shipped
            // if (! $order->canShip()) {
            //     $this->messageManager->addError(__('You can\'t create an shipment.'));
            // }

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
                'number' => 'TORD23254WERZXd3', // Replace with your tracking number
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