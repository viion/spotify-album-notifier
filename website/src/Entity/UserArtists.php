<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=UserArtistsRepository::class)
 */
class UserArtists
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
    private $userId;
    
    public function getId()
    {
        return $this->id;
    }
    
    public function setId($id)
    {
        $this->id = $id;
        
        return $this;
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
    
    public function getUserId()
    {
        return $this->userId;
    }
    
    public function setUserId($userId)
    {
        $this->userId = $userId;
        
        return $this;
    }
}
