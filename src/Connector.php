<?php

namespace ActiveCampaign;


use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Exception\ConnectException;


class Connector
{

    protected $base_url;
    protected $api_key;
    protected $paginate_params;
    protected $filter_params;
    protected $query_params;
    protected $orderby_params;


    public function __construct($base_url, $api_key)
    {
        $this->api_key			= $api_key;
        $this->base_url 		= rtrim($base_url, '/') . '/';
        $this->paginate_params 	= [];
        $this->filter_params 	= [];
        $this->query_params 	= [];
        $this->orderby_params 	= [];
    }

    public function paginate($limit, $offset = 0)
    {
        $this->paginate_params = ['limit' => $limit, 'offset' => $offset];

        return $this;
    }

    public function filter($values)
    {
        foreach($values as $key => $value){
            $this->filter_params['filters[' . $key . ']'] = $value;
        }

        return $this;
    }

    public function query($values)
    {
        $this->query_params = $values;

        return $this;
    }

    public function orderby($values)
    {
        foreach($values as $key => $value){
            $this->orderby_params['orders[' . $key . ']'] = $value;
        }

        return $this;
    }

    protected function buildUrl($endpoint)
    {
        $query = http_build_query(array_merge($this->query_params, $this->filter_params, $this->orderby_params, $this->paginate_params));

        return $this->base_url . $endpoint . (!empty($query) ? ((stripos($endpoint, '?') === false ? '?' : '&') . $query) : '');
    }

    protected function request($method, $endpoint, $data = [])
    {
        try {
            $client		= new Client(['headers' => ['Api-Token' => $this->api_key]]);
            $url		= $this->buildUrl($endpoint);
            $options	= !empty($data) ? ['json' => $data] : [];
            $request 	= $client->request($method, $url, $options);

            return json_decode($request->getBody()->getContents(), true);

        } catch (ClientException $exception) {
            // echo $exception->getMessage();
            $response = $exception->getResponse();
            $responseBodyAsString = $response->getBody()->getContents();

            $message = json_decode($responseBodyAsString, true);
            var_dump($message);

        } catch (ServerException $exception) {
            echo $exception->getMessage();
        } catch (ConnectException $exception) {
            $errorArr = $exception->getHandlerContext();
            echo $errorArr['error'];
        }

        die;
    }

}