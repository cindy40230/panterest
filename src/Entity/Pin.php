<?php

namespace App\Entity;


use App\Repository\PinRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Table\Entity;

/**
 * @ORM\Entity(repositoryClass=PinRepository::class)
 * @ORM\Table(name="pins")
 *  @ORM\HasLifecycleCallbacks()
 */
class Pin
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $description;

    /**
     * @ORM\Column(type="datetime",options={"default":"CURRENT_TIMESTAMP"})
     */
    private $createdAt;


    /**
     * @ORM\Column(type="datetime",options={"default":"CURRENT_TIMESTAMP"})
     */
    private $udaptedAt;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }


    public function getUdaptedAt(): ?\DateTimeInterface
    {
        return $this->udaptedAt;
    }

    public function setUdaptedAt(\DateTimeInterface $udaptedAt): self
    {
        $this->udaptedAt = $udaptedAt;

        return $this;
    }

    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     *
     */
    
    public function  udapteTimestamps(){
        //on appelle cette methode avant de persister (creation dun pin) et avant une mise a jour(modificationdu pin)
        // on appelle les methodes:
         if ($this->getCreatedAt()=== null ){//si createdAt n'a pas de valeur tu lui en met une 
            
            $this->setCreatedAt(new \DateTimeImmutable);//la date et l heure actuelle avec \DateTimeImmutable dtae non modifiable
         }
          
           $this ->setUdaptedAt(new \DateTimeImmutable);

    }
}
