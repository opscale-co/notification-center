<?php

namespace Opscale\NotificationCenter\Nova\Cards;

use Laravel\Nova\Card;

class NotificationCard extends Card
{
    /**
     * The width of the card (1/3, 1/2, or full).
     *
     * @var string
     */
    public $width = '1/3';

    /**
     * The card data.
     */
    protected string $title = '';

    protected string $subtitle = '';

    protected string $actionLabel = 'View';

    protected ?string $actionUrl = null;

    protected string $actionTarget = '_self';

    protected string $variant = 'primary';

    /**
     * Get the component name for the card.
     */
    public function component(): string
    {
        return 'notification-card';
    }

    /**
     * Set the card title.
     */
    public function title(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Set the card subtitle.
     */
    public function subtitle(string $subtitle): static
    {
        $this->subtitle = $subtitle;

        return $this;
    }

    /**
     * Set the action button label.
     */
    public function actionLabel(string $label): static
    {
        $this->actionLabel = $label;

        return $this;
    }

    /**
     * Set the action button URL.
     */
    public function actionUrl(string $url): static
    {
        $this->actionUrl = $url;

        return $this;
    }

    /**
     * Set the action button target.
     */
    public function actionTarget(string $target): static
    {
        $this->actionTarget = $target;

        return $this;
    }

    /**
     * Set the card variant.
     */
    public function variant(string $variant): static
    {
        $this->variant = $variant;

        return $this;
    }

    /**
     * Prepare the card for JSON serialization.
     */
    public function jsonSerialize(): array
    {
        return array_merge(parent::jsonSerialize(), [
            'title' => $this->title,
            'subtitle' => $this->subtitle,
            'actionLabel' => $this->actionLabel,
            'actionUrl' => $this->actionUrl,
            'actionTarget' => $this->actionTarget,
            'variant' => $this->variant,
        ]);
    }
}
