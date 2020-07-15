<?php

namespace App\Service\Spotify;

use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\ResponseInterface;

class API
{
    const AUTH_ENDPOINT = 'https://accounts.spotify.com';
    const AUTH_API_TOKEN = '/api/token';
    const AUTH_AUTHORIZE = '/authorize';
    
    const API_ENDPOINT = 'https://api.spotify.com/v1';
    
    const API_ME = '/me';
    const API_ME_FOLLOWING = '/me/following';
    
    const API_ARTIST_ALBUMS = '/artists/%s/albums';
    
    
    /** @var HttpClient */
    private $client;
    
    public function __construct()
    {
        $this->client = HttpClient::create();
    }
    
    /**
     * Perform an API Request
     */
    protected function request(string $method, string $endpoint, array $options = []): ResponseInterface
    {
        try {
            $response = $this->client->request($method, $endpoint, $options);
        } catch (ClientException $ex) {
            print_r([
                'exception_content_response' => $ex->getResponse()->getContent()
            ]);
            throw $ex;
        }
        
        if ($response->getStatusCode() !== 200) {
            print_r([
                'error_content_response' => $response->getContent()
            ]);
            throw new \Exception("Request to: {$endpoint} failed, http error code: {$response->getStatusCode()}");
        }
        
        return $response;
    }
    
    /**
     * Get Author Header for API (Server to Server)
     */
    protected function getAuthBearerHeader()
    {
        return [
            'Authorization' => 'Bearer ' . SpotifyConfig::token()
        ];
    }
    
    /**
     * Get Author Header for API (Client to Server)
     */
    protected function getAuthBasicHeader()
    {
        return [
            'Authorization' => 'Basic ' . base64_encode(
                SpotifyConfig::id() . ':' . SpotifyConfig::secret()
            )
        ];
    }
}
