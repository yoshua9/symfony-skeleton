<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Reservation
{
    #[ORM\Column(type: 'string')]
    private string $locator;

    #[ORM\Column(type: 'string')]
    private string $guest;

    #[ORM\Column(type: 'date')]
    private DateTime $checkin;

    #[ORM\Column(type: 'date')]
    private DateTime $checkout;

    #[ORM\Column(type: 'string')]
    private string $hotel;

    #[ORM\Column(type: 'float')]
    private float $price;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $actions = null;

    public function __construct(array $data)
    {
        $this->locator = $data['locator'];
        $this->guest = $data['guest'];
        $this->checkin = new DateTime($data['checkin']);
        $this->checkout = new DateTime($data['checkout']);
        $this->hotel = $data['hotel'];
        $this->price = (float)$data['price'];
        $this->actions = $data['actions'] ?? null;
    }

    // Getters and Setters
    public function getLocator(): string { return $this->locator; }
    public function setLocator(string $locator): self { $this->locator = $locator; return $this; }

    public function getGuest(): string { return $this->guest; }
    public function setGuest(string $guest): self { $this->guest = $guest; return $this; }

    public function getCheckin(): DateTime { return $this->checkin; }
    public function setCheckin(DateTime $checkin): self { $this->checkin = $checkin; return $this; }

    public function getCheckout(): DateTime { return $this->checkout; }
    public function setCheckout(DateTime $checkout): self { $this->checkout = $checkout; return $this; }

    public function getHotel(): string { return $this->hotel; }
    public function setHotel(string $hotel): self { $this->hotel = $hotel; return $this; }

    public function getPrice(): float { return $this->price; }
    public function setPrice(float $price): self { $this->price = $price; return $this; }

    public function getActions(): ?string { return $this->actions; }
    public function setActions(?string $actions): self { $this->actions = $actions; return $this; }
}