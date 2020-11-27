<?php

namespace App\Entity\Traits;

trait Timestampable{

   /**
     * @ORM\Column(type="datetime",options={"default":"CURRENT_TIMESTAMP"})
     */
    private $createdAt;


    /**
     * @ORM\Column(type="datetime",options={"default":"CURRENT_TIMESTAMP"})
     */
    private $udaptedAt;

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
