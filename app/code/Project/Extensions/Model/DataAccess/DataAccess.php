<?php
namespace Project\Extensions\Model\DataAccess;

use Zend\Http\Client;

class DataAccess
{
    public function Post($uri, $arr)
    {
        try 
        {
            //document: https://framework.zend.com/manual/2.4/en/modules/zend.http.client.html
            $client = new Client();
            $client->setUri($uri);
            $client->setOptions(array('maxredirects' => 0, 'timeout' => 30));
            $client->setParameterPost($arr);
            $client->setMethod('POST');
            
            $response = $client->send();
            return json_decode($response->getbody(), true);
        }
        catch (\Zend\Http\Exception\RuntimeException $runtimeException) 
        {
            return $runtimeException->getMessage();
        }
    }
}