<?php
    /**
     * Created by PhpStorm
     * @package Liujinyong\Weather
     * User: Jack
     * Date: 2021/4/24
     * Time: 7:09 下午
     */

    namespace Liujinyong\Weather;


    use GuzzleHttp\Client;
    use Liujinyong\Weather\Exceptions\HttpException;
    use Liujinyong\Weather\Exceptions\InvalidArgumentException;

    class Weather
    {
        protected $key;

        protected $guzzleOptions = [];

        /**
         * Weather constructor.
         *
         * @param $key
         */
        public function __construct($key)
        {
            $this->key = $key;
        }

        /**
         * @return \GuzzleHttp\Client
         * author Fox
         */
        public function getHttpClient()
        {
            return new Client($this->guzzleOptions);
        }

        /**
         * @param array $options
         * author Fox
         */
        public function setGuzzleOptions(array $options)
        {
            $this->guzzleOptions = $options;
        }

        /**
         * @param        $city
         * @param string $type
         * @param string $format
         * 获取数据
         * @return mixed|string
         * author Fox
         * @throws \GuzzleHttp\Exception\GuzzleException
         * @throws \Liujinyong\Weather\Exceptions\HttpException
         * @throws \Liujinyong\Weather\Exceptions\InvalidArgumentException
         */
        public function getWeather($city, string $type = 'base', string $format = 'json')
        {
            $url = 'https://restapi.amap.com/v3/weather/weatherInfo';

            //判断参数异常
            if (!in_array(strtolower($format),['xml','json'])){
                throw new InvalidArgumentException('Invalid response format: '.$format);
            }
            if (!in_array(strtolower($type),['base','all'])){
                throw new InvalidArgumentException("'Invalid type value(base/all): ".$type);
            }
            $query = array_filter([
                'key' => $this->key,
                'city' => $city,
                'output' => $format,
                'extensions' =>  $type,
            ]);

            try {
                $response = $this->getHttpClient()->get($url, [
                    'query' => $query,
                ])->getBody()->getContents();

                return 'json' === $format ? \json_decode($response, true) : $response;
            }catch (\Exception $e){
                throw new HttpException($e->getMessage(),$e->getCode(),$e);
            }



        }
    }