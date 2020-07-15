<?php

namespace App\Service\Discord;

use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Token\AccessTokenInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Wohali\OAuth2\Client\Provider\Discord;

class DiscordOAuth
{
    const SUCCESS_REDIRECT_URL = '/discord/login/success';
    
    /** @var ParameterBagInterface */
    private $param;
    
    /** @var Discord */
    private $discord;
    
    public function __construct(Request $request, ParameterBagInterface $param)
    {
        $this->param = $param;
        
        $this->discord = new Discord([
            'clientId'      => $param->get('discord_id'),
            'clientSecret'  => $param->get('discord_secret'),
            'redirectUri'   => $request->getScheme() .'://'. $request->getHost() . self::SUCCESS_REDIRECT_URL
        ]);
    }

    public function getLoginAuthorizationUrl(Request $request): string
    {
        $url = $this->discord->getAuthorizationUrl([
            'scope' => $this->param->get('discord_scopes')
        ]);
        
        $request->getSession()->set('state', $this->discord->getState());
        
        return $url;
    }
    
    public function setLoginAuthorizationState(Request $request)
    {
        // check CSRF
        if ($request->get('state') !== $request->getSession()->get('state')) {
            throw new \Exception('Invalid CSRF state');
        }
    
        /** @var AccessToken $token */
        $token = $this->discord->getAccessToken('authorization_code', [
            'code' => $request->get('code')
        ]);
    
        $request->getSession()->set('discord', $token->jsonSerialize());
        
        $user = $this->discord->getResourceOwner($token);
        
        return $this->getSsoAccess($token, $user);
    }
    
    /**
     * Get authorization token
     */
    public function getAuthorizationToken(Request $request): \stdClass
    {
        $token = $request->getSession()->get('discord');
        
        // if expired, refresh the token
        if ($token['expires'] < time()) {
            return $this->refreshAuthorizationToken();
        }
        
        $token = new AccessToken($token);
        $user = $this->discord->getResourceOwner($token);
        
        return $this->getSsoAccess($token, $user);
    }
    
    /**
     * Refresh the authorization token
     */
    public function refreshAuthorizationToken(Request $request): \stdClass
    {
        $token = $request->getSession()->get('discord');
        
        $token = $this->discord->getAccessToken('refresh_token', [
            'refresh_token' => $token->refresh_token
        ]);
        
        $user = $this->discord->getResourceOwner($token);
        
        return $this->getSsoAccess($token, $user);
    }
    
    /**
     * get information from the SSO
     */
    public function getSsoAccess(AccessTokenInterface $token, $user): \stdClass
    {
        $obj                  = (Object)[];
        $obj->name            = 'discord';
        $obj->id              = $user->getId();
        $obj->username        = $user->getUsername();
        $obj->email           = $user->getEmail() ?: 'none';
        $obj->avatar          = $user->getAvatarHash();
        $obj->tokenExpires    = $token->getExpires();
        $obj->tokenAccess     = $token->getToken();
        $obj->tokenRefresh    = $token->getRefreshToken();
        return $obj;
    }
}
