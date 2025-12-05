<?php
namespace Api\Actions;

interface ActionInterface
{
    public function execute(array $params, ?array $user = null): array;
    public function requiresAuth(): bool;
}
