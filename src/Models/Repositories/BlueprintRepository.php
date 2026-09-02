<?php

namespace Opscale\NotificationCenter\Models\Repositories;

use Opscale\NotificationCenter\Models\Enums\NotificationType;

trait BlueprintRepository
{
    /**
     * The blueprint fields that may contain {{ variable }} placeholders.
     *
     * @var array<int, string>
     */
    protected array $interpolatableFields = ['subject', 'body', 'summary', 'action'];

    /**
     * The regular expression used to detect {{ variable }} placeholders.
     */
    protected string $variablePattern = '/\{\{\s*([\w.]+)\s*\}\}/';

    /**
     * Get the unique dynamic variable names declared across the blueprint content
     * (subject, body, summary and action), in order of first appearance.
     *
     * @return array<int, string>
     */
    public function variables(): array
    {
        $names = [];

        foreach ($this->interpolatableFields as $field) {
            if (preg_match_all($this->variablePattern, (string) $this->{$field}, $matches)) {
                foreach ($matches[1] as $name) {
                    $names[$name] = true;
                }
            }
        }

        return array_keys($names);
    }

    /**
     * Build the attributes for a concrete Notification by substituting the given
     * values into every {{ variable }} placeholder. Missing values resolve to ''.
     *
     * The notification is always Transactional and never expires; a blueprint
     * does not carry a type or expiration of its own.
     *
     * @param  array<string, mixed>  $values
     * @return array<string, mixed>
     */
    public function toNotificationAttributes(array $values = []): array
    {
        $resolve = fn (?string $content): ?string => $content === null ? null : preg_replace_callback(
            $this->variablePattern,
            fn (array $match): string => (string) ($values[$match[1]] ?? ''),
            $content,
        );

        return [
            'subject' => $resolve($this->subject),
            'body' => $resolve($this->body),
            'summary' => $resolve($this->summary),
            'action' => $resolve($this->action),
            'type' => NotificationType::TRANSACTIONAL,
        ];
    }
}
