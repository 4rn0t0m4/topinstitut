<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\City;
use App\Models\Department;
use App\Models\Establishment;

class PrestationVilleController extends Controller
{
    private const TYPE_SLUGS = [
        'institut-de-beaute' => 0,
        'estheticienne-a-domicile' => 1,
        'spa' => 2,
        'thalasso' => 3,
    ];

    public function __invoke(string $deptSlug, string $citySlug, string $prestationSlug)
    {
        $department = Department::where('slug', $deptSlug)->firstOrFail();
        $city = City::where('slug', $citySlug)
            ->where('department_code', $department->code)
            ->firstOrFail();

        $query = Establishment::active()->where('city_id', $city->id);

        if (isset(self::TYPE_SLUGS[$prestationSlug])) {
            $typeId = self::TYPE_SLUGS[$prestationSlug];
            $query->where('type', $typeId);
            $prestationName = Establishment::TYPE_LABELS[$typeId];
        } else {
            $category = Category::where('slug', $prestationSlug)->firstOrFail();
            $query->whereHas('categories', fn ($q) => $q->where('categories.id', $category->id));
            $prestationName = $category->name;
        }

        $establishments = $query->with(['schedules', 'photos'])
            ->orderByDesc('rating')
            ->paginate(20);

        if ($establishments->isEmpty() && ! request()->filled('page')) {
            abort(404);
        }

        return view('prestation-ville.show', compact(
            'establishments', 'city', 'department', 'prestationName', 'prestationSlug'
        ));
    }
}
