<?php

namespace App\Model\IpStack;

class Location
{
    protected int $geoNameId;

    protected string $capital;

    protected array $languages;

    protected string $countryFlag;

    protected string $countryFlagEmoji;

    protected string $countryFlagEmojiUnicode;

    protected string $callingCode;

    public function getGeoNameId(): ?int
    {
        return $this->geoNameId;
    }

    public function setGeoNameId(int $geoNameId): Location
    {
        $this->geoNameId = $geoNameId;

        return $this;
    }

    public function getCapital(): ?string
    {
        return $this->capital;
    }

    public function setCapital(string $capital): Location
    {
        $this->capital = $capital;

        return $this;
    }

    public function getLanguages(): ?array
    {
        return $this->languages;
    }

    public function setLanguages(?array $languages): Location
    {
        $this->languages = $languages;

        return $this;
    }

    public function getCountryFlag(): ?string
    {
        return $this->countryFlag;
    }

    public function setCountryFlag(string $countryFlag): Location
    {
        $this->countryFlag = $countryFlag;

        return $this;
    }

    public function getCountryFlagEmoji(): ?string
    {
        return $this->countryFlagEmoji;
    }

    public function setCountryFlagEmoji(string $countryFlagEmoji): Location
    {
        $this->countryFlagEmoji = $countryFlagEmoji;

        return $this;
    }

    public function getCountryFlagEmojiUnicode(): ?string
    {
        return $this->countryFlagEmojiUnicode;
    }

    public function setCountryFlagEmojiUnicode(string $countryFlagEmojiUnicode): Location
    {
        $this->countryFlagEmojiUnicode = $countryFlagEmojiUnicode;

        return $this;
    }

    public function getCallingCode(): ?string
    {
        return $this->callingCode;
    }

    public function setCallingCode(string $callingCode): Location
    {
        $this->callingCode = $callingCode;

        return $this;
    }
}