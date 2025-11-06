<?php

namespace App\Model\IpStack;

class Response
{
    protected string $ip;

    protected string $type;

    protected string $continentCode;

    protected string $continentName;

    protected string $countryCode;

    protected string $countryName;

    protected string $regionCode;

    protected string $regionName;

    protected string $city;

    protected string $zip;

    protected float $latitude;

    protected float $longitude;

    protected string $radius;

    protected string $ipRoutingType;

    protected string $connectionType;

    protected Location $location;

    public function getIp(): ?string
    {
        return $this->ip;
    }

    public function setIp(string $ip): Response
    {
        $this->ip = $ip;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): Response
    {
        $this->type = $type;

        return $this;
    }

    public function getContinentCode(): ?string
    {
        return $this->continentCode;
    }

    public function setContinentCode(string $continentCode): Response
    {
        $this->continentCode = $continentCode;

        return $this;
    }

    public function getContinentName(): ?string
    {
        return $this->continentName;
    }

    public function setContinentName(string $continentName): Response
    {
        $this->continentName = $continentName;

        return $this;
    }

    public function getCountryCode(): ?string
    {
        return $this->countryCode;
    }

    public function setCountryCode(string $countryCode): Response
    {
        $this->countryCode = $countryCode;

        return $this;
    }

    public function getCountryName(): ?string
    {
        return $this->countryName;
    }

    public function setCountryName(string $countryName): Response
    {
        $this->countryName = $countryName;

        return $this;
    }

    public function getRegionCode(): ?string
    {
        return $this->regionCode;
    }

    public function setRegionCode(string $regionCode): Response
    {
        $this->regionCode = $regionCode;

        return $this;
    }

    public function getRegionName(): ?string
    {
        return $this->regionName;
    }

    public function setRegionName(string $regionName): Response
    {
        $this->regionName = $regionName;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): Response
    {
        $this->city = $city;

        return $this;
    }

    public function getZip(): ?string
    {
        return $this->zip;
    }

    public function setZip(string $zip): Response
    {
        $this->zip = $zip;

        return $this;
    }

    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    public function setLatitude(float $latitude): Response
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    public function setLongitude(float $longitude): Response
    {
        $this->longitude = $longitude;

        return $this;
    }

    public function getRadius(): ?string
    {
        return $this->radius;
    }

    public function setRadius(string $radius): Response
    {
        $this->radius = $radius;

        return $this;
    }

    public function getIpRoutingType(): ?string
    {
        return $this->ipRoutingType;
    }

    public function setIpRoutingType(string $ipRoutingType): Response
    {
        $this->ipRoutingType = $ipRoutingType;

        return $this;
    }

    public function getConnectionType(): ?string
    {
        return $this->connectionType;
    }

    public function setConnectionType(string $connectionType): Response
    {
        $this->connectionType = $connectionType;

        return $this;
    }

    public function getLocation(): ?Location
    {
        return $this->location;
    }

    public function setLocation(?Location $location): Response
    {
        $this->location = $location;

        return $this;
    }
}