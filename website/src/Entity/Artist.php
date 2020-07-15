<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ArtistRepository::class)
 */
class Artist
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
    /**
     * @ORM\Column(type="text")
     */
    private $artistData;

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
    
    public function getArtistData()
    {
        return json_decode($this->artistData, true);
    }
    
    public function setArtistData($artistData)
    {
        $this->artistData = json_encode($artistData);
        
        return $this;
    }
}
