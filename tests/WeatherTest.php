<?php
    /**
     * Created by PhpStorm
     * @package Liujinyong\Weather\tests
     * User: Jack
     * Date: 2021/4/24
     * Time: 8:59 下午
     */

    namespace Liujinyong\Weather\tests;


    use GuzzleHttp\Client;
    use GuzzleHttp\ClientInterface;
    use GuzzleHttp\Psr7\Response;
    use Liujinyong\Weather\Exceptions\HttpException;
    use Liujinyong\Weather\Exceptions\InvalidArgumentException;
    use Liujinyong\Weather\Weather;
    use Mockery\Matcher\AnyArgs;
    use PHPUnit\Framework\TestCase;

    class WeatherTest extends TestCase
    {
        protected $key = "f37b6c1baff61003d94b3333ace4c919";

        //TODO 编写但愿测试的时候  没有做依赖模拟    五一的时候看下
        public function testGetWeather()
        {
            $response = new Response(200,[],'{"success":true}');
            $client = \Mockery::mock(Client::class);
            $client->allows()->get('https://restapi.amap.com/v3/weather/weatherInfo', [
                'query' => [
                    'key' => $this->key,
                    'city' => '120104',
                    'output' => 'json',
                    'extensions' => 'base',
                ]
            ])->andReturn($response);
            $w = \Mockery::mock(Weather::class,[$this->key])->makePartial();
            $w->allows()->getHttpClient()->andReturn($client);
            $this->assertSame(['success' => true], $w->getWeather('120104'));

            $response = new Response(200, [], '<hello>content</hello>');
            $client = \Mockery::mock(Client::class);
            $client->allows()->get('https://restapi.amap.com/v3/weather/weatherInfo', [
                'query' => [
                    'key' => $this->key,
                    'city' => '120104',
                    'extensions' => 'all',
                    'output' => 'xml',
                ],
            ])->andReturn($response);

            $w = \Mockery::mock(Weather::class, [$this->key])->makePartial();
            $w->allows()->getHttpClient()->andReturn($client);

            $this->assertSame('<hello>content</hello>', $w->getWeather('120104', 'all', 'xml'));




        }

        public function testGetHttpClient()
        {
                $w = new Weather($this->key);
                $this->assertInstanceOf(ClientInterface::class,$w->getHttpClient());
        }

        public function testSetGuzzleOptions()
        {
            $w = new Weather($this->key);

            // 设置参数前，timeout 为 null
            $this->assertNull($w->getHttpClient()->getConfig('timeout'));

            // 设置参数
            $w->setGuzzleOptions(['timeout' => 5000]);

            // 设置参数后，timeout 为 5000
            $this->assertSame(5000, $w->getHttpClient()->getConfig('timeout'));
        }
        //检查$type参数
        public function testGetWeatherWithInvalidType()
        {
            $w = new Weather($this->key);
            $this->expectException(InvalidArgumentException::class);
            $this->expectExceptionMessage('Invalid type value(base/all): foo');
            $w->getWeather('120104','foo');
            $this->fail('Failed to assert getWeather throw exception with invalid argument.');
        }
        //检查$format 参数
        public function testGetWeatherWithInvalidFormat()
        {
            $w = new Weather($this->key);
            $this->expectException(InvalidArgumentException::class);
            $this->expectExceptionMessage('Invalid response format: array');
            $w->getWeather('120104','base','array');
            $this->fail('Failed to assert getWeather throw exception with invalid argument.');
        }
        public function testGetWeatherWithGuzzleRuntimeException()
        {
            $client = \Mockery::mock(Client::class);
            $client->allows()
                ->get(new AnyArgs()) // 由于上面的用例已经验证过参数传递，所以这里就不关心参数了。
                ->andThrow(new \Exception('request timeout')); // 当调用 get 方法时会抛出异常。

            $w = \Mockery::mock(Weather::class, [$this->key])->makePartial();
            $w->allows()->getHttpClient()->andReturn($client);

            // 接着需要断言调用时会产生异常。
            $this->expectException(HttpException::class);
            $this->expectExceptionMessage('request timeout');

            $w->getWeather('深圳');
        }

    }