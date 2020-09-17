<?php
namespace Project\Extensions\Model;

use Project\Extensions\Model\DataAccess\DataAccess as dataAccess;

class Scg extends DataAccess
{
    const COOKIE_NAME = 'scg-express';
    const COOKIE_DURATION = 86400;
    const COOKIE_PATH = '/';
    private $uri = 'https://scgyamatodev.flare.works';
    private $username = 'info_test@scgexpress.co.th';
    private $password = 'Initial@1234';
    
    protected $cookieManager;
    protected $cookieMetadataFactory;

    
  
    public function __construct(
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory
    )
    {
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
    }

    public function Authentication()
    {
        return $this->Post(
            $this->uri.'/api/authentication',
            array(
                'username' => $this->username,
                'password' => $this->password
            ));
    }

    public function PlaceOrder($shipperCode, $shipperName, $shipperTel, $shipperAddress, $shipperZipcode,
        $deliveryAddress, $zipcode, $contactName, $tel, $orderCode,
        $totalBoxs, $orderDate)
    {
        return $this->TryPost(
            $this->uri.'/api/orderwithouttrackingnumber',
            array(
                'token' => $this->GetCookie(self::COOKIE_NAME),
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
        return $this->TryPost(
            $this->uri.'/api/getMobileLabel',
            array(
                'token' => $this->GetCookie(self::COOKIE_NAME),
                'tracking_number' => $tracking_numbers
            ));

        //return $this->uri.'/api/getMobileLabel?token='.$_COOKIE[$cookie_name].'&tracking_number='.$tracking_numbers;
    }

    function TryPost($uri, $param)
    {
        // no cookie data, create new
        if(is_null($this->GetCookie(self::COOKIE_NAME)) ||
            $this->GetCookie(self::COOKIE_NAME) == '')
        {
            $response = $this->Authentication();
            if($response['status'])
            {   // create new cookie
                $this->CreateCookie(
                    self::COOKIE_NAME,
                    self::COOKIE_DURATION,
                    self::COOKIE_PATH,
                    $response['token']);

                // update token param with a new value
                $param['token'] = $response['token'];
            }
        }

        $response = $this->Post($uri, $param);

        if(isset($response['status']) && 
            !$response['status'] && $response['message'] == 'token is not valid')
        {   
            $response = $this->Authentication();
            if($response['status'])
            {   
                // re-create new cookie
                $this->CreateCookie(
                    self::COOKIE_NAME,
                    self::COOKIE_DURATION,
                    self::COOKIE_PATH,
                    $response['token']);
                
                // update token param with a new value
                $param['token'] = $response['token'];
                $response = $this->Post($uri, $param);
            }    
        }

        return $response;
    }

    function GetCookie($key)
    { 
        return $this->cookieManager->getCookie($key);
    }

    function CreateCookie($key, $duration, $path, $value): void
    {
        $metadata = $this->cookieMetadataFactory
            ->createPublicCookieMetadata()
            ->setDuration($duration)
            ->setPath($path);

        $this->cookieManager->setPublicCookie($key, $value, $metadata);
    }
}