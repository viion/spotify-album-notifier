<?php

namespace App\Service\Spotify;

class Spotify extends API
{
    // -- User API --
    
    public function setApiToken()
    {
        $endpoint = API::AUTH_ENDPOINT . API::AUTH_API_TOKEN;
        
        $response = $this->request('POST', $endpoint, [
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            'body' => [
                'grant_type'    => 'client_credentials',
                'client_id'     => SpotifyConfig::id(),
                'client_secret' => SpotifyConfig::secret(),
            ]
        ]);
        
        $response = $response->toArray();
        $response['created'] = time();

        SpotifyConfig::set($response);
    }
    
    public function setApiUserToken(string $code)
    {
        $endpoint = API::AUTH_ENDPOINT . API::AUTH_API_TOKEN;
    
        $response = $this->request('POST', $endpoint, [
            'headers' => $this->getAuthBasicHeader(),
            'body' => [
                'grant_type'    => 'authorization_code',
                'code'          => $code,
                'redirect_uri'  => SpotifyConfig::redirect(),
            ]
        ]);
    
        $response = $response->toArray();
        $response['created'] = time();
    
        SpotifyConfig::set($response);
    }
    
    public function requestLoginAuthorization(): string
    {
        $endpoint = API::AUTH_ENDPOINT . API::AUTH_AUTHORIZE;
        
        $query = http_build_query([
            'response_type' => 'code',
            'client_id'     => SpotifyConfig::id(),
            'redirect_uri'  => SpotifyConfig::redirect(),
            'scope'         => SpotifyConfig::scopes(),
        ]);

        $url = $endpoint . '?' . $query;
        
        return $url;
    }
    
    public function refreshToken()
    {
        $endpoint = API::AUTH_ENDPOINT . API::AUTH_API_TOKEN;

        $response = $this->request('POST', $endpoint, [
            'headers' => $this->getAuthBasicHeader(),
            'body' => [
                'grant_type'    => 'refresh_token',
                'refresh_token' => SpotifyConfig::refreshToken(),
            ]
        ]);
    
        $response = $response->toArray();
        $response['created'] = time();
    
        SpotifyConfig::set($response);
    }
    
    // -- Core API --
    
    public function getUserProfile()
    {
        $endpoint = API::API_ENDPOINT . API::API_ME;
    
        $response = $this->request('GET', $endpoint, [
            'headers' => $this->getAuthBearerHeader(),
        ]);
    
        return $response->toArray();
    }
    
    public function getUserFollowedArtists(?string $cursor = null)
    {
        $endpoint = API::API_ENDPOINT . API::API_ME_FOLLOWING;
    
        $query = [
            'type' => 'artist',
            'limit' => 50,
        ];
        
        if ($cursor) {
            $query['after'] = $cursor;
        }
        
        $response = $this->request('GET', $endpoint, [
            'headers' => $this->getAuthBearerHeader(),
            'query' => $query,
        ]);
    
        return $response->toArray();
    }
    
    public function getArtistAlbums(string $artistId, int $offset = 0)
    {
        $endpoint = API::API_ENDPOINT . API::API_ARTIST_ALBUMS;
        $endpoint = sprintf($endpoint, $artistId);
    
        $query = [
            'include_groups' => 'album,single',
            'limit' => 5,
        ];
    
        if ($offset) {
            $query['offset'] = $offset;
        }
    
        $response = $this->request('GET', $endpoint, [
            'headers' => $this->getAuthBearerHeader(),
            'query' => $query,
        ]);
    
        return $response->toArray();
    }
}
