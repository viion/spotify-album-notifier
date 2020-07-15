<?php

namespace App\Service\Commands;

use App\Entity\Artist;
use App\Repository\ArtistRepository;
use App\Repository\UserArtistsRepository;
use App\Repository\UserRepository;
use App\Service\Discord;
use App\Service\Spotify\Spotify;
use App\Service\Spotify\SpotifyConfig;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ScanArtists
{
    const LAST_TIME = __DIR__ .'/RecordArtistLastDate.txt';
    
    private $param;
    private $em;
    private $userRepository;
    private $userArtistsRepository;
    private $artistRepository;
    private $output;
    private $discord;
    
    public function __construct(
        ParameterBagInterface $param,
        EntityManagerInterface $em,
        UserRepository $userRepository,
        UserArtistsRepository $userArtistsRepository,
        ArtistRepository $artistRepository
    ) {
        $this->param = $param;
        $this->em = $em;
        $this->userRepository = $userRepository;
        $this->userArtistsRepository = $userArtistsRepository;
        $this->artistRepository = $artistRepository;
        $this->output = new ConsoleOutput();
        $this->discord = new Discord();
    }
    
    public function scan()
    {
        $lastTime = trim(file_get_contents(self::LAST_TIME));
        
        // set spotify config
        SpotifyConfig::set([
            'client_id' => $this->param->get('spotify_id'),
            'client_secret' => $this->param->get('spotify_secret'),
        ]);
        
        $artists = $this->artistRepository->findAll();
        
        $this->output->writeln("Scanning artists for albums");
    
        $spotify = new Spotify();
        $spotify->setApiToken();
        
        /** @var Artist $artist */
        foreach ($artists as $artist) {
            $this->output->writeln("Get artist: {$artist->getArtistName()}");
            
            $albums = $spotify->getArtistAlbums($artist->getArtistId());
            $albumsData = [];
            $albumsNew = [];
            
            foreach ($albums['items'] as $album) {
                $data = [
                    'id'    => $album['id'],
                    'name'  => $album['name'],
                    'date'  => $album['release_date'],
                    'url'   => $album['external_urls']['spotify'],
                    'image' => $album['images'][2]['url'],
                ];
                
                $albumsData[] = $data;
                
                if ($lastTime < $album['release_date']) {
                    $this->output->writeln("New Album! {$album['name']} - Release date: {$album['release_date']}");
    
                    $albumsNew[] = $data;
                }
            }
            
            $artist->setArtistData($albumsData);
            
            $this->em->persist($artist);
            $this->em->flush();
            
            if ($albumsNew) {
                $text = "";
                
                foreach ($albumsNew as $na) {
                    $text .= "*{$na['name']}* \n {$na['url']} \n\n";
                }
                
                $this->discord->sendMessage(732678236529754162, null, [
                    'title' => "New: {$artist->getArtistName()}",
                    'description' => $text,
                    'color' => hexdec('42f563'),
                ]);
            }
    
            usleep(100000);
        }
        
        // save date
        file_put_contents(self::LAST_TIME, date('Y-m-d'));
    }
}
