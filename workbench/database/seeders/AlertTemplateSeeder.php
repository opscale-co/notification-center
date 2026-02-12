<?php

namespace Workbench\Database\Seeders;

use Illuminate\Database\Seeder;
use Opscale\NotificationCenter\Nova\Notification;
use Opscale\NovaDynamicResources\Models\Enums\TemplateType;
use Opscale\NovaDynamicResources\Models\Field;
use Opscale\NovaDynamicResources\Models\Template;

class AlertTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $template = Template::create([
            'label' => 'Alerts',
            'singular_label' => 'Alert',
            'uri_key' => 'alerts',
            'title' => 'target',
            'type' => TemplateType::Inherited->value,
            'related_class' => Notification::class,
        ]);

        Field::create([
            'template_id' => $template->id,
            'type' => 'title',
            'label' => 'Target',
            'name' => 'target',
            'required' => false,
            'rules' => ['nullable', 'string', 'max:255'],
        ]);
    }
}
