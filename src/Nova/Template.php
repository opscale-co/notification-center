<?php

namespace Opscale\NotificationCenter\Nova;

use Illuminate\Database\Eloquent\Builder;
use Laravel\Nova\Http\Requests\NovaRequest;
use Opscale\NovaDynamicResources\Nova\Template as BaseTemplate;

class Template extends BaseTemplate
{
    /**
     * Build an "index" query for the given resource.
     *
     * @param  Builder<\Opscale\NovaDynamicResources\Models\Template>  $query
     * @return Builder<\Opscale\NovaDynamicResources\Models\Template>
     */
    public static function indexQuery(NovaRequest $request, $query): Builder
    {
        return parent::indexQuery($request, $query)
            ->where('related_class', Notification::class);
    }
}
