<?php

namespace Opscale\NotificationCenter;

use Illuminate\Http\Request;
use Laravel\Nova\Menu\MenuGroup;
use Laravel\Nova\Menu\MenuItem;
use Laravel\Nova\Menu\MenuSection;
use Laravel\Nova\Nova;
use Laravel\Nova\Tool as NovaTool;
use Opscale\NotificationCenter\Nova\Audience;
use Opscale\NotificationCenter\Nova\Notification;
use Opscale\NotificationCenter\Nova\Profile;
use Opscale\NotificationCenter\Nova\Template;
use Opscale\NovaDynamicResources\Models\Template as TemplateModel;

class Tool extends NovaTool
{
    public function boot()
    {
        // Nova::script('notification-center', __DIR__ . '/../dist/js/tool.js');
        // Nova::style('notification-center', __DIR__ . '/../dist/css/tool.css');
    }

    public function menu(Request $request)
    {
        $notificationItems = TemplateModel::where('related_class', Notification::class)
            ->instantiables()
            ->get()
            ->map(fn (TemplateModel $template) => MenuItem::make($template->label)
                ->path('/resources/' . $template->uri_key))
            ->all();

        return MenuSection::make(__('Notification Center'), [
            MenuGroup::make(__('Notifications'), [
                ...$notificationItems,
                MenuItem::resource(Notification::class),
                MenuItem::resource(Template::class),
            ]),
            MenuGroup::make(__('Audience'), [
                MenuItem::resource(Profile::class),
                MenuItem::resource(Audience::class),
            ]),
        ])->icon('bell')->collapsable();
    }
}
