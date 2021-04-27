<?php
    /**
     * Created by PhpStorm
     * @package Liujinyong\Weather
     * User: Jack
     * Date: 2021/4/25
     * Time: 10:19 下午
     */

    namespace Liujinyong\Weather;


    class ServiceProvider extends \Illuminate\Support\ServiceProvider
    {
        protected $defer = true;

        public function register()
        {
            $this->app->singleton(Weather::class, function(){
                return new Weather(config('services.weather.key'));
            });

            $this->app->alias(Weather::class, 'weather');
        }

        public function provides()
        {
            return [Weather::class, 'weather'];
        }
    }