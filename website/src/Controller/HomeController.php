<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\Discord\DiscordOAuth;
use App\Service\Discord\DiscordUser;
use App\Service\Spotify\Spotify;
use Delight\Cookie\Cookie;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Uuid;
use App\Service\Spotify\SpotifyConfig;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /** @var EntityManagerInterface */
    private $em;
    
    /** @var ParameterBagInterface */
    private $param;
    
    /** @var DiscordUser */
    private $discordUser;
    
    public function __construct(EntityManagerInterface $em, ParameterBagInterface $param, DiscordUser $discordUser)
    {
        $this->em = $em;
        $this->param = $param;
        $this->discordUser = $discordUser;
    }
    
    /**
     * @Route("/", name="home")
     */
    public function index()
    {
        return $this->render('account.html.twig');
    }
    
    /**
     * @Route("/discord/login", name="discord_login")
     */
    public function discordLogin(Request $request)
    {
        $discord = new DiscordOAuth($request, $this->param);
        
        $url = $discord->getLoginAuthorizationUrl($request);
        
        return $this->redirect($url);
    }
    
    /**
     * @Route("/discord/login/success", name="discord_login_success")
     */
    public function discordLoginSuccess(Request $request)
    {
        $discord = new DiscordOAuth($request, $this->param);
        
        $sso = $discord->setLoginAuthorizationState($request);
        
        $user = $this->em->getRepository(User::class)->findOneBy([
            'discordId' => $sso->id,
        ]);
    
        $user = $user ?: new User();
        $user
            ->setUsername($sso->username)
            ->setDiscordId($sso->id)
            ->setData($sso)
            ->setSession(Uuid::uuid4()->toString());
        
        $this->em->persist($user);
        $this->em->flush();
    
        // set cookie
        $cookie = new Cookie(DiscordUser::COOKIE_NAME);
        
        $cookie->setValue($user->getSession())->setMaxAge(DiscordUser::COOKIE_LENGTH)->setPath('/')->save();
        
        return $this->redirectToRoute('home');
    }
    
    /**
     * @Route("/spotify/login", name="spotify_login")
     */
    public function spotifyLogin(Request $request)
    {
        $this->setSpotifyConfig($request);
    
        $spotify = new Spotify();
    
        $url = $spotify->requestLoginAuthorization();
        
        return $this->redirect($url);
    }
    
    /**
     * @Route("/spotify/login/success", name="spotify_login_success")
     */
    public function spotifyLoginSuccess(Request $request)
    {
        $this->setSpotifyConfig($request);
    
        $spotify = new Spotify();
        
        // Set API User Token
        $spotify->setApiUserToken($request->get('code'));
        
        $config = SpotifyConfig::get();
        
        $user = $this->discordUser->user();
        
        $user->setSpotify($config);
    
        $this->em->persist($user);
        $this->em->flush();
        
        return $this->redirectToRoute('home');
    }
    
    private function setSpotifyConfig(Request $request)
    {
        // Set our credentials
        SpotifyConfig::set([
            'client_id'     => $this->param->get('spotify_id'),
            'client_secret' => $this->param->get('spotify_secret'),
            'scopes'        => $this->param->get('spotify_scopes'),
            'redirect'      => $request->getScheme() .'://'. $request->getHost() . $this->generateUrl('spotify_login_success')
        ]);
    }
    
    /**
     * @Route("/spotify/artists/get", name="spotify_artists_get")
     */
    public function getSpotifyArtists(Request $request)
    {
        $spotify = new Spotify();
        
        $artists = $spotify->getUserFollowedArtists();
        
        print_r($artists);
        die;
    }
}
