<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ArtistAlbumsRepository::class)
 */
class ArtistAlbums
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;
    /**
     * @ORM\Column(type="string", length=255)
     */
    private $artistId;
    /**
     * @ORM\Column(type="string", length=255)
     */
    private $artistName;

    public function getId(): ?int
    {
        return $this->id;
    }
    
    public function getArtistId()
    {
        return $this->artistId;
    }
    
    public function setArtistId($artistId)
    {
        $this->artistId = $artistId;
        
        return $this;
    }
    
    public function getArtistName()
    {
        return $this->artistName;
    }
    
    public function setArtistName($artistName)
    {
        $this->artistName = $artistName;
        
        return $this;
    }
}
