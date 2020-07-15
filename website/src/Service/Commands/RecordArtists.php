<?php

namespace App\Service\Commands;

use App\Entity\Artist;
use App\Entity\User;
use App\Entity\UserArtists;
use App\Repository\ArtistRepository;
use App\Repository\UserArtistsRepository;
use App\Repository\UserRepository;
use App\Service\Spotify\Spotify;
use App\Service\Spotify\SpotifyConfig;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Output\ConsoleOutput;

class RecordArtists
{
    private $em;
    private $userRepository;
    private $userArtistsRepository;
    private $artistRepository;
    private $output;
    
    const TEMP = [
        'total' => 0,
        'artists' => [],
    ];
    
    private $temp;
    
    public function __construct(
        EntityManagerInterface $em,
        UserRepository $userRepository,
        UserArtistsRepository $userArtistsRepository,
        ArtistRepository $artistRepository
    ) {
        $this->em = $em;
        $this->userRepository = $userRepository;
        $this->userArtistsRepository = $userArtistsRepository;
        $this->artistRepository = $artistRepository;
        $this->output = new ConsoleOutput();
    }
    
    public function collect()
    {
        $users = $this->userRepository->findAll();
        
        /** @var User $user */
        foreach ($users as $user) {
            SpotifyConfig::set($user->getSpotify());
            
            $spotify = new Spotify();
            $spotify->refreshToken();
            
            // reset temp
            $this->temp = self::TEMP;
            
            // start
            $this->output->writeln("Getting first batch of artists");
            
            $artists = $spotify->getUserFollowedArtists();
            $cursor  = $artists['artists']['cursors']['after'];
    
            $this->temp['total'] = $artists['artists']['total'];
            
            $this->handleArtists($user, $artists['artists']['items']);
            
            $this->output->writeln("Total: {$this->temp['total']}");
            
            while($this->temp['total'] > 0) {
                $this->output->writeln("---> Cursor: {$cursor}");
                
                $artists = $spotify->getUserFollowedArtists($cursor);
                $cursor  = $artists['artists']['cursors']['after'];
                
                $this->handleArtists($user, $artists['artists']['items']);
                
                sleep(1);
            }
            
            // clear out any non followed artists.
            // $this->temp['artists'];
    
            $this->em->flush();
        }
    }
    
    private function handleArtists(User $user, array $artists) {
        foreach ($artists as $artist) {
            $this->temp['total']--;
            
            $artistId   = $artist['id'];
            $artistName = $artist['name'];
            
            $this->temp['artists'][] = $artistId;
        
            $exists = $this->artistRepository->findOneBy([ 'artistId' => $artistId ]);
        
            if (!$exists) {
                $new = new Artist();
                $new->setArtistId($artistId);
                $new->setArtistName($artistName);
            
                $this->em->persist($new);
                $this->output->writeln("Remaining: {$this->temp['total']} -- New Artist: {$artistName}");
            }
        
            $exists = $this->userArtistsRepository->findOneBy([
                'userId'   => $user->getId(),
                'artistId' => $artistId
            ]);
        
            if (!$exists) {
                $new = new UserArtists();
                $new->setArtistId($artistId);
                $new->setUserId($user->getId());
            
                $this->em->persist($new);
            }
        }
    }
}
