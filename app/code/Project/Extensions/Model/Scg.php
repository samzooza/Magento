<?php
namespace Project\Extensions\Model;

use Project\Extensions\Model\DataContext;

class Scg extends DataContext
{
    private $uri = 'https://scgyamatodev.flare.works';
    private $username = 'info_test@scgexpress.co.th';
    private $password = 'Initial@1234';

    public function Authentication()
    {
        return $this->Post(
            $this->uri.'/api/authentication',
            array(
                'username' => $this->username,
                'password' => $this->password));
    }

    public function PlaceOrder($token, $shipperCode, $shipperName, $shipperTel, $shipperAddress,
        $shipperZipcode, $deliveryAddress, $zipcode, $contactName, $tel,
        $orderCode, $totalBoxs, $orderDate)
    {
        return $this->Post(
            $this->uri.'/api/orderwithouttrackingnumber',
            array(
                'token' => $token,
                'ShipperCode' => $shipperCode,
                'ShipperName' => $shipperName,
                'ShipperTel' => $shipperTel,
                'ShipperAddress' => $shippingaddress,
                'ShipperZipcode' => $shipperZipcode,
                'DeliveryAddress' => $deliveryAddress,
                'Zipcode' => $zipcode,
                'ContactName' => $contactName,
                'Tel' => $tel,
                'OrderCode' => $orderDate,
                'TotalBoxs' => $totalBoxs,
                'OrderDate' => $orderDate));
    }
}