## Support us

At Opscale, we’re passionate about contributing to the open-source community by providing solutions that help businesses scale efficiently. If you’ve found our tools helpful, here are a few ways you can show your support:

⭐ **Star this repository** to help others discover our work and be part of our growing community. Every star makes a difference!

💬 **Share your experience** by leaving a review on [Trustpilot](https://www.trustpilot.com/review/opscale.co) or sharing your thoughts on social media. Your feedback helps us improve and grow!

📧 **Send us feedback** on what we can improve at [feedback@opscale.co](mailto:feedback@opscale.co). We value your input to make our tools even better for everyone.

🙏 **Get involved** by actively contributing to our open-source repositories. Your participation benefits the entire community and helps push the boundaries of what’s possible.

💼 **Hire us** if you need custom dashboards, admin panels, internal tools or MVPs tailored to your business. With our expertise, we can help you systematize operations or enhance your existing product. Contact us at hire@opscale.co to discuss your project needs.

Thanks for helping Opscale continue to scale! 🚀



## Description

Make sure your users get notified. Notification Center for Laravel Nova gives you multi-channel delivery strategies with automatic channel escalation, smart retries, and advanced open/action tracking — so no message goes unnoticed.

![Demo](https://raw.githubusercontent.com/opscale-co/notification-center/refs/heads/main/screenshots/notification-center.gif)

## Installation

[![Latest Version on Packagist](https://img.shields.io/packagist/v/opscale-co/notification-center.svg?style=flat-square)](https://packagist.org/packages/opscale-co/notification-center)

You can install the package in to a Laravel app that uses [Nova](https://nova.laravel.com) via composer:

```bash

composer require opscale-co/notification-center

```

Next up, you must register the tool with Nova. This is typically done in the `tools` method of the `NovaServiceProvider`.

```php

// in app/Providers/NovaServiceProvider.php
// ...
public function tools()
{
    return [
        // ...
        new \Opscale\NotificationCenter\Tool(),
    ];
}

```

## Usage

Publish the configuration and run the migrations:

```bash
php artisan notification-center:install
```

## Configuration

The configuration file `config/notification-center.php` is organized around four features:

### Templates

Notifications use templates from [Nova Dynamic Resources](https://github.com/opscale-co/nova-dynamic-resources) to define their fields. Refer to the composition example for setting up templates.

### Orchestration

Define delivery strategies per notification type (marketing, transactional, system, alert, reminder). Each strategy configures an ordered list of channels to attempt (e.g., `['webpush', 'whatsapp', 'sms']`) with automatic channel escalation — if a channel times out, the next one in the list is attempted. Map channel identifiers to notification classes in the `messages` config to control how each channel renders its content.

### Deliverability

Fine-tune when and how notifications reach your users. Configure time windows (allowed days and hours), retry intervals with escalating delays, max attempts per channel, and channel timeout thresholds. The scheduler re-dispatches strategies hourly for all published, non-expired notifications. Web push requires HTTPS since service workers only work in secure contexts. For WhatsApp, set your Twilio Content Template SID via the `TWILIO_WHATSAPP_CONTENT_SID` environment variable.

### Segmentation

Target the right users with audiences. Create **static** audiences with manually curated profiles, **dynamic** audiences that resolve membership at query time based on criteria rules, or **segments** as named reusable cohorts based on shared profile attributes. Attach one or more audiences to a notification when publishing.

### In-App Notifications

Three built-in channels deliver notifications directly inside your application:

- **Nova**: Sends notifications through Laravel Nova's native notification bell. Users receive real-time alerts within the Nova admin panel.
- **Card**: Renders notifications as visual cards on the **Notifications Dashboard**, a dedicated Nova dashboard where users can browse and manage their card notifications.
- **Web Push**: Delivers browser push notifications via service workers. Requires HTTPS — service workers are only available in secure contexts. Make sure your application is served over HTTPS in all environments where web push is enabled.

### Tracking

Every delivery generates unique open and action slugs for built-in tracking. Tracking routes are registered automatically. Enable Google Analytics integration by setting your GA4 Measurement ID via the `GOOGLE_ANALYTICS_ID` environment variable.

## Testing

``` bash

npm run test

```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](https://github.com/opscale-co/.github/blob/main/CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email development@opscale.co instead of using the issue tracker.

## Credits

- [Opscale](https://github.com/opscale-co)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.