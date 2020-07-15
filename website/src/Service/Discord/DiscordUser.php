<?php

namespace App\Service\Discord;

use App\Entity\User;
use App\Service\Spotify\Spotify;
use App\Service\Spotify\SpotifyConfig;
use Delight\Cookie\Cookie;
use Doctrine\ORM\EntityManagerInterface;

class DiscordUser
{
    const COOKIE_NAME = 'session';
    const COOKIE_LENGTH = (60 * 60 * 24 * 30);
    
    /** @var EntityManagerInterface */
    private $em;
    
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        
        // attempt to get user, this sets Spotify config
        $this->user();
        
        // refresh any tokens
        $this->refreshSpotifyToken();
    }
    
    public function user(): ?User
    {
        $session = Cookie::get(self::COOKIE_NAME);
    
        if (!$session || $session === 'x') {
            return null;
        }
        
        /** @var User $user */
        $user = $this->em->getRepository(User::class)->findOneBy([
            'session' => $session,
        ]);
        
        // set our spotify info
        if ($user->getSpotify()) {
            SpotifyConfig::set($user->getSpotify());
        }
    
        return $user;
    }
    
    public function refreshSpotifyToken()
    {
        $user = $this->user();
        
        if (!$user || !$user->getSpotify()) {
            return;
        }
        
        $expired = $user->getSpotify()['created'] + $user->getSpotify()['expires_in'];
        
        // if not yet expired, do nuttin
        if ($expired > time()) {
            return;
        }
        
        $spotify = new Spotify();
        $spotify->refreshToken();
        
        // save new config
        $config = SpotifyConfig::get();

        // set new spotify config
        
        $user->setSpotify($config);
    
        // save
        $this->em->persist($user);
        $this->em->flush();
    }
    
    public function isOnline()
    {
        $session = Cookie::get(self::COOKIE_NAME);
        
        if (!$session || $session === 'x') {
            return false;
        }
        
        $user = $this->em->getRepository(User::class)->findOneBy([
            'session' => $session,
        ]);
        
        if (!$user) {
            return false;
        }
        
        return true;
    }
}
