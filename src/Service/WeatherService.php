<?php

namespace App\Service;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;

class WeatherService
{
    private string $apiKey ='c254001f0f2a23d71745d80d4fd561bc';
    private ClientInterface $client;
    private RequestFactoryInterface $requestFactory;

    public function __construct(string $apiKey, ClientInterface $client, RequestFactoryInterface $requestFactory)
    {
        $this->apiKey = $apiKey;
        $this->client = $client;
        $this->requestFactory = $requestFactory;
    }

    public function getWeather(string $city): array
    {
        $url = "https://api.openweathermap.org/data/2.5/weather?q=$city&appid=$this->apiKey&units=metric";
        $request = $this->requestFactory->createRequest('GET', $url);
        $response = $this->client->sendRequest($request);
        $data = json_decode($response->getBody()->getContents(), true);
        return $data;
    }
}
