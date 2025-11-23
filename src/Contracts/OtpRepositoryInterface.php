<?php

namespace Gangon\Otp\Contracts;

interface OtpRepositoryInterface
{
    public function store(string $key, string $hash, int $expiry): void;
    public function get(string $key): ?string;
    public function delete(string $key): void;
    public function incrementAttempts(string $key): void;
    public function getAttempts(string $key): int;
}
