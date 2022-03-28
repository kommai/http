<?php

declare(strict_types=1);

namespace Kommai\Http;

use RuntimeException;

class Session
{
    public string $id; // readonly
    public array $data;

    public function __construct(array $options = [])
    {
        $status = session_status();
        if ($status === PHP_SESSION_DISABLED) {
            throw new RuntimeException('Session is disabled');
        }
        if ($status === PHP_SESSION_NONE || !isset($_SESSION)) {
            if (session_start($options) === false) {
                throw new RuntimeException('Failed to start a session');
            }
        }
        if (!isset($this->id)) {
            $this->id = session_id();
        }
        if (!isset($this->data)) {
            $this->data = &$_SESSION;
        }
    }

    public function renew(): self
    {
        session_regenerate_id();
        $this->id = session_id();
        return $this;
    }
}
