<?php declare(strict_types=1);

namespace App\Model;

class Response
{
    private bool $success;

    private array $content;

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function setSuccess(bool $success): Response
    {
        $this->success = $success;

        return $this;
    }

    public function getContent(): array
    {
        return $this->content;
    }

    public function setContent(array $content): Response
    {
        $this->content = $content;

        return $this;
    }
}