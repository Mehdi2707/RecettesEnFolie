<?php

namespace App\Entity\Trait;

use Doctrine\ORM\Mapping as ORM;
trait ConsultedAtTrait
{
    #[ORM\Column(options: ['default' => 'CURRENT_TIMESTAMP'])]
    private ?\DateTimeImmutable $consultedAt = null;

    public function getConsultedAt(): ?\DateTimeImmutable
    {
        return $this->consultedAt;
    }

    public function setConsultedAt(\DateTimeImmutable $consultedAt): self
    {
        $this->consultedAt = $consultedAt;

        return $this;
    }
}