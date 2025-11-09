<?php declare(strict_types=1);

namespace App\Model;

class RequestBulk
{
    private string $action;

    private array $ipAddresses;

    public function getAction(): string
    {
        return $this->action;
    }

    public function setAction(string $action): RequestBulk
    {
        $this->action = $action;

        return $this;
    }

    public function getIpAddresses(): array
    {
        return $this->ipAddresses;
    }

    public function setIpAddresses(array $ipAddresses): RequestBulk
    {
        $this->ipAddresses = $ipAddresses;

        return $this;
    }
}