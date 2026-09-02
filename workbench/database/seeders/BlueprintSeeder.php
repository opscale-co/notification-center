<?php

namespace Workbench\Database\Seeders;

use Illuminate\Database\Seeder;
use Opscale\NotificationCenter\Models\Blueprint;

class BlueprintSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Blueprint::create([
            'name' => 'Order Shipped',
            'subject' => 'Your order {{ order_id }} has shipped',
            'body' => '<p>Hi {{ name }}, good news! Your order <strong>{{ order_id }}</strong> is on its way '
                . 'and should arrive by {{ delivery_date }}.</p>',
            'summary' => 'Order {{ order_id }} is on its way',
            'action' => 'https://example.com/orders/{{ order_id }}/track',
        ]);

        Blueprint::create([
            'name' => 'Welcome',
            'subject' => 'Welcome to {{ company }}, {{ name }}!',
            'body' => '<p>Hi {{ name }}, thanks for joining <strong>{{ company }}</strong>. '
                . 'We are excited to have you on board.</p>',
            'summary' => 'Welcome aboard, {{ name }}',
            'action' => 'https://example.com/welcome',
        ]);
    }
}
