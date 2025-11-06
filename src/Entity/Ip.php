<?php

namespace App\Entity;

use App\Repository\IpRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: IpRepository::class)]
class Ip
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 250)]
    private ?string $ip = null;

    #[ORM\Column(length: 50)]
    private ?string $type = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $continentCode = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $continentName = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $countryCode = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $countryName = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $regionCode = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $regionName = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $city = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $zip = null;

    #[ORM\Column(nullable: true)]
    private ?float $latitude = null;

    #[ORM\Column(nullable: true)]
    private ?float $longitude = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $radius = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $ipRoutingType = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $connectionType = null;

    #[ORM\Column(nullable: true)]
    private ?int $geoNameId = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $capital = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $languageCode = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $languageName = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $languageNameNative = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $countryFlag = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $countryFlagEmoji = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $countryFlagEmojiUnicode = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $callingCode = null;

    #[ORM\Column]
    private ?DateTimeImmutable $updatedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIp(): ?string
    {
        return $this->ip;
    }

    public function setIp(string $ip): static
    {
        $this->ip = $ip;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getContinentCode(): ?string
    {
        return $this->continentCode;
    }

    public function setContinentCode(?string $continentCode): static
    {
        $this->continentCode = $continentCode;

        return $this;
    }

    public function getContinentName(): ?string
    {
        return $this->continentName;
    }

    public function setContinentName(?string $continentName): static
    {
        $this->continentName = $continentName;

        return $this;
    }

    public function getCountryCode(): ?string
    {
        return $this->countryCode;
    }

    public function setCountryCode(?string $countryCode): static
    {
        $this->countryCode = $countryCode;

        return $this;
    }

    public function getCountryName(): ?string
    {
        return $this->countryName;
    }

    public function setCountryName(?string $countryName): static
    {
        $this->countryName = $countryName;

        return $this;
    }

    public function getRegionCode(): ?string
    {
        return $this->regionCode;
    }

    public function setRegionCode(?string $regionCode): static
    {
        $this->regionCode = $regionCode;

        return $this;
    }

    public function getRegionName(): ?string
    {
        return $this->regionName;
    }

    public function setRegionName(?string $regionName): static
    {
        $this->regionName = $regionName;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): static
    {
        $this->city = $city;

        return $this;
    }

    public function getZip(): ?string
    {
        return $this->zip;
    }

    public function setZip(?string $zip): static
    {
        $this->zip = $zip;

        return $this;
    }

    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    public function setLatitude(?float $latitude): static
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    public function setLongitude(?float $longitude): static
    {
        $this->longitude = $longitude;

        return $this;
    }

    public function getRadius(): ?string
    {
        return $this->radius;
    }

    public function setRadius(?string $radius): static
    {
        $this->radius = $radius;

        return $this;
    }

    public function getIpRoutingType(): ?string
    {
        return $this->ipRoutingType;
    }

    public function setIpRoutingType(?string $ipRoutingType): static
    {
        $this->ipRoutingType = $ipRoutingType;

        return $this;
    }

    public function getConnectionType(): ?string
    {
        return $this->connectionType;
    }

    public function setConnectionType(?string $connectionType): static
    {
        $this->connectionType = $connectionType;

        return $this;
    }

    public function getGeoNameId(): ?int
    {
        return $this->geoNameId;
    }

    public function setGeoNameId(?int $geoNameId): static
    {
        $this->geoNameId = $geoNameId;

        return $this;
    }

    public function getCapital(): ?string
    {
        return $this->capital;
    }

    public function setCapital(?string $capital): static
    {
        $this->capital = $capital;

        return $this;
    }

    public function getLanguageCode(): ?string
    {
        return $this->languageCode;
    }

    public function setLanguageCode(?string $languageCode): static
    {
        $this->languageCode = $languageCode;

        return $this;
    }

    public function getLanguageName(): ?string
    {
        return $this->languageName;
    }

    public function setLanguageName(?string $languageName): static
    {
        $this->languageName = $languageName;

        return $this;
    }

    public function getLanguageNameNative(): ?string
    {
        return $this->languageNameNative;
    }

    public function setLanguageNameNative(?string $languageNameNative): static
    {
        $this->languageNameNative = $languageNameNative;

        return $this;
    }

    public function getCountryFlag(): ?string
    {
        return $this->countryFlag;
    }

    public function setCountryFlag(?string $countryFlag): static
    {
        $this->countryFlag = $countryFlag;

        return $this;
    }

    public function getCountryFlagEmoji(): ?string
    {
        return $this->countryFlagEmoji;
    }

    public function setCountryFlagEmoji(?string $countryFlagEmoji): static
    {
        $this->countryFlagEmoji = $countryFlagEmoji;

        return $this;
    }

    public function getCountryFlagEmojiUnicode(): ?string
    {
        return $this->countryFlagEmojiUnicode;
    }

    public function setCountryFlagEmojiUnicode(?string $countryFlagEmojiUnicode): static
    {
        $this->countryFlagEmojiUnicode = $countryFlagEmojiUnicode;

        return $this;
    }

    public function getCallingCode(): ?string
    {
        return $this->callingCode;
    }

    public function setCallingCode(?string $callingCode): static
    {
        $this->callingCode = $callingCode;

        return $this;
    }

    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}
