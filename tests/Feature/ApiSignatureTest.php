<?php

namespace Tests\Feature;

use Tests\TestCase;

use YiDeJia\Signature\Api\Facade\RequestFacade;
use YiDeJia\Signature\Api\Http\RequestProxy;

class ApiSignatureTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testBasicTest()
    {
        
        $this->assertTrue(true);
    }


    public function testLaravelServer()
    {
        $proxy = (new RequestProxy);
        $proxy->setConfig([
            'enable' => true,                       //是否可用
            'name' => '测试',                        //您的应用名称
            'key' => 'test-key',                    //您的应用的key
            'secret' => 'test-secret',              //您的应用的secret
            'secret_backup' => 'test-secret-1',     //您的应用的备用secret，用于更新配置时使用
            'secret_backup_enable' => false,         //是否启用备用secret, 只有启用了配置的备用secret才会有效<安全更新secret>
            'algo' => 'sha256',                     //缺省时，默认为sha1
            'expired' => -1,                        //签名有效时长，单位：秒，负数或零表示无限制
            'base_url' => 'http://127.0.0.1:9501',  //api地址host, 例如： http://127.0.0.1:9501
        ]);
        $result = $proxy->get('api/demo', ['refresh' => 0]);

        print_r([

            'result_code' => $result->getCode(),
            'result_message' => $result->getMessage(),
            'result_data' => $result->getData(),
        
            'request_url' => $result->getRequestUrl(),
            'request_headers' => $result->getRequestHeaders(),
            'request_signature_params' => $result->getRequestSignatureParams(),
        
            'response_code' => $result->getResponseCode(),
            // 'response_headers' => $result->getResponseHeaders(),
            'response_content' => $result->getResponseContent(),
        ]);

        $this->assertTrue(true);
    }

    public function testLaravelFacad()
    {
        // $proxy = RequestFacade::setConfig([
        //     'enable' => true,                       //是否可用
        //     'name' => '测试',                        //您的应用名称
        //     'key' => 'test-key',                    //您的应用的key
        //     'secret' => 'test-secret',              //您的应用的secret
        //     'secret_backup' => 'test-secret-1',     //您的应用的备用secret，用于更新配置时使用
        //     'secret_backup_enable' => false,        //是否启用备用secret, 只有启用了配置的备用secret才会有效<安全更新secret>
        //     'algo' => 'sha256',                     //缺省时，默认为sha1
        //     'expired' => -1,                        //签名有效时长，单位：秒，负数或零表示无限制
        //     'base_url' => 'http://127.0.0.1:8000',  //api地址host, 例如： http://127.0.0.1:9501
        // ]);
        // $result = $proxy->get('api/demo', ['refresh' => 0]);

        // print_r([

        //     'result_code' => $result->getCode(),
        //     'result_message' => $result->getMessage(),
        //     'result_data' => $result->getData(),
        
        //     'request_url' => $result->getRequestUrl(),
        //     'request_headers' => $result->getRequestHeaders(),
        //     'request_signature_params' => $result->getRequestSignatureParams(),
        
        //     'response_code' => $result->getResponseCode(),
        //     // 'response_headers' => $result->getResponseHeaders(),
        //     'response_content' => $result->getResponseContent(),
        // ]);

        $this->assertTrue(true);
    }
}
