<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 */
class User
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
    private $username;
    /**
     * @ORM\Column(type="string", length=255)
     */
    private $session;
    /**
     * @ORM\Column(type="string")
     */
    private $discordId;
    /**
     * @ORM\Column(type="text")
     */
    private $data;
    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $spotify;
    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $artists;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }
    
    public function getSession()
    {
        return $this->session;
    }
    
    public function setSession($session)
    {
        $this->session = $session;
        
        return $this;
    }

    public function getDiscordId(): ?string
    {
        return $this->discordId;
    }

    public function setDiscordId(string $discordId): self
    {
        $this->discordId = $discordId;

        return $this;
    }
    
    public function getData()
    {
        return json_decode($this->data, true);
    }
    
    public function setData($data)
    {
        $this->data = json_encode($data);
        
        return $this;
    }
    
    public function getSpotify()
    {
        return json_decode($this->spotify, true);
    }
    
    public function setSpotify($spotify)
    {
        $this->spotify = json_encode($spotify);
        
        return $this;
    }
    
    public function getArtists()
    {
        return json_decode($this->artists, true);
    }
    
    public function setArtists($artists)
    {
        $this->artists = json_encode($artists);
        
        return $this;
    }
}
