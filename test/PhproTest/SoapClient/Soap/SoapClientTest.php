<?php

namespace PhproTest\SoapClient\Soap;

use Phpro\SoapClient\Soap\SoapClient as PhproSoapClient;
use Phpro\SoapClient\Soap\SoapFunction as PhproSoapFunction;

/**
 * Class SoapClientTest
 *
 * @package PhproTest\SoapClient\Soap
 */
class SoapClientTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Wheather API
     */
    const CDYNE_WSDL = 'http://wsf.cdyne.com/WeatherWS/Weather.asmx?WSDL';

    /**
     * @var PhproSoapClient
     */
    protected $client;

    /**
     * Configure client
     */
    function setUp()
    {
        $this->client = new PhproSoapClient(self::CDYNE_WSDL, ['soap_version' => SOAP_1_2]);
    }

    /**
     * @test
     */
    function it_should_know_all_WSDL_types()
    {
        $types = $this->client->getSoapTypes();

        $this->assertTrue(array_key_exists('GetCityForecastByZIP', $types));
        $this->assertEquals('string', $types['GetCityForecastByZIP']['ZIP']);
    }

    /**
     * @test
     */
    function it_should_know_all_WSDL_functions() 
    {
        $known = [
            new PhproSoapFunction(
                'GetWeatherInformation',
                [ '$parameters' => 'GetWeatherInformation' ],
                'GetWeatherInformationResponse'    
            ),
            new PhproSoapFunction(
                'GetCityForecastByZIP',
                [ '$parameters' => 'GetCityForecastByZIP' ],    
                'GetCityForecastByZIPResponse'
            ),
            new PhproSoapFunction(
                'GetCityWeatherByZIP',
                [ '$parameters' => 'GetCityWeatherByZIP' ],
                'GetCityWeatherByZIPResponse'
            )
        ];
        
        $functions = $this->client->getSoapFunctions();        
        
        $this->assertCount(3, $functions);
        foreach ($known as $function) {
            $this->assertContains($function, $functions, null, false, false);
        }
    }
    
    /**
     * @test
     * @vcr vcr-enabled.yml
     *
     * Note: this method will throw Exceptions if VCR can't take over the configured SoapClient.
     */
    function it_should_be_possible_to_hook_php_vcr_for_testing()
    {
        $result = $this->client->GetCityWeatherByZIP(['ZIP' => '10013']);
        $this->assertTrue($result->GetCityWeatherByZIPResult->Success);
    }
}
