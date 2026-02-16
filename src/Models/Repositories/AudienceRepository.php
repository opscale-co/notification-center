<?php

namespace Opscale\NotificationCenter\Models\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Opscale\NotificationCenter\Models\Enums\AudienceType;
use Opscale\NotificationCenter\Models\Profile;

trait AudienceRepository
{
    public function getProfiles(): Collection
    {
        return match ($this->type) {
            AudienceType::STATIC => $this->profiles,
            AudienceType::DYNAMIC => $this->getDynamicProfiles(),
            AudienceType::SEGMENT => $this->getSegmentProfiles(),
        };
    }

    protected function getDynamicProfiles(): Collection
    {
        $profileIds = DB::select($this->criteria);

        return Profile::whereIn('id', collect($profileIds)->pluck('id'))->get();
    }

    protected function getSegmentProfiles(): Collection
    {
        $tags = array_map('trim', explode(',', $this->criteria));

        return Profile::withAnyTags($tags)->get();
    }
}
