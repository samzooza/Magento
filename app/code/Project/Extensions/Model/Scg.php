<?php
namespace Project\Extensions\Model;

use Project\Extensions\Model\DataAccess\DataAccess as dataAccess;

class Scg extends DataAccess
{
    const URI = 'https://scgyamatodev.flare.works';
    const USERNAME = 'info_test@scgexpress.co.th';
    const PASSWORD = 'Initial@1234';
    private $token = '';

    public function GetKey()
    {
        return $this->Post(
            self::URI.'/api/authentication',
            array(
                'username' => self::USERNAME,
                'password' => self::PASSWORD
            ));
    }

    public function PlaceOrder($shipperCode, $shipperName, $shipperTel, $shipperAddress, $shipperZipcode,
        $deliveryAddress, $zipcode, $contactName, $tel, $orderCode,
        $totalBoxs, $orderDate)
    {
        return $this->PostWithKey(
            self::URI.'/api/orderwithouttrackingnumber',
            array(
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

    public function GetMobileLabel($tracking_numbers)
    {
        $response = $this->GetKey();
        if($response['status'])
            return self::URI.'/api/getMobileLabel?token='.$response['token'].'&tracking_number='.$tracking_numbers;
    
        return $response;
    }

    function PostWithKey($uri, $param)
    {
        $response = $this->GetKey();
        if($response['status'])
        {
            $param['token'] = $response['token'];
            return $this->Post($uri, $param);
        }

        return $response;
    }
}