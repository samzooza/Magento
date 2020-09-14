<?php
namespace Project\Extensions\Model;

use Project\Extensions\Model\DataAccess\DataAccess as dataAccess;

class Scg extends DataAccess
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
                'password' => $this->password
            ));
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
                'ShipperAddress' => $shipperAddress,
                'ShipperZipcode' => $shipperZipcode,
                'DeliveryAddress' => $deliveryAddress,
                'Zipcode' => $zipcode,
                'ContactName' => $contactName,
                'Tel' => $tel,
                'OrderCode' => $orderCode,
                'TotalBoxs' => $totalBoxs,
                'OrderDate' => $orderDate
            ));
    }

    public function GetMobileLabel($token, $tracking_numbers)
    {
        return $this->uri.'/api/getMobileLabel?token='.$token.'&tracking_number='.$tracking_numbers;
    }
}