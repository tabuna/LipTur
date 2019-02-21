<?php

namespace App\Orchid\Entities\Many;

use App\Core\Traits\ManyTypeTrait;

use App\Traits\FilmTrait;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Orchid\Press\Entities\Many;
use Orchid\Platform\Http\Forms\Posts\BasePostForm;
use Orchid\Platform\Http\Forms\Posts\UploadPostForm;
use Orchid\Screen\TD;

use Orchid\Screen\Fields\InputField;
use Orchid\Screen\Fields\TinyMCEField;
use Orchid\Screen\Fields\DateTimerField;
use Orchid\Screen\Fields\MapField;
use App\Fields\RegionField;
use Orchid\Screen\Fields\TextAreaField;
use Orchid\Screen\Fields\TagsField;

class FilmType extends Many
{
    use ManyTypeTrait;
    use FilmTrait;

    /**
     * @var string
     */
    public $name = 'Фильмы';

    /**
     * @var string
     */
    public $slug = 'film';

    /**
     * @var string
     */
    public $icon = 'fa fa-video-camera';

    /**
     * @var string
     */
    public $image = '/img/category/agencie.jpg';

    /**
     * Slug url /news/{name}.
     *
     * @var string
     */
    public $slugFields = 'Name';

    /**
     * @var bool
     */
    public $category = false;

    /**
     * Display global maps.
     *
     * @var bool
     */
    public $maps = false;

    /**
     * @var array
     */
    public function filters(): array
    {
        return [];
    }

    /**
     * @var string
     */
    public $viewTemplate = 'pages.film';

    /**
     * Rules Validation.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'id' => 'sometimes|integer|unique:posts',
        ];
    }

    /**
     * @return array
     */
    public function fields(): array
    {
        return [
            InputField::make('Name')
                ->type('text')
                ->max(255)
                ->title('Название')
                ->help('Название фильма'),
            TinyMCEField::make('body')
                ->max(255)
                ->rows(10)
                ->theme('modern'),
            InputField::make('Director')
                ->type('text')
                ->max(255)
                ->title('Режиссер')
                ->help('Режиссер фильма'),
            InputField::make('Country')
                ->type('text')
                ->max(255)
                ->title('Страна')
                ->help('Страна производства'),
        ];
    }

    /**
     * Grid View for post type.
     */
    public function grid(): array
    {
        return [
            TD::set('publish_at', 'Дата публикации'),
            TD::set('created_at', 'Дата создания'),
        ];
    }

    /**
     * @return array
     */
    public function modules()
    {
        return [
            BasePostForm::class,
            UploadPostForm::class,
        ];
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function display()
    {
        return collect([
            'name' => 'Фильмы',
            'icon' => 'icon-lip-passport',
            'svg' => '/dist/svg/maps/shrines.svg',
            'mapUrl' => false,
            'time' => false,
        ]);
    }

    /**
     * @return string
     */
    public function route(): string
    {
        return 'item';
    }

    /**
     * @param $data
     *
     * @return array
     */
    public function getAdditionalData($data)
    {
        return Cache::remember('movie-id-' . $data['movieId'], 20, function () use ($data) {
            $lowerCityName = env('RAMBLER_CITY_NAME', 'липецк');

            $city = $this->getCity($lowerCityName);

            $schedule = $this->getSchedule($city->CityID, $data['movieId'])->List;

            $theaterIds = [];
            foreach ($schedule as $item) {
                $theaterIds[] = $item->PlaceObjectID;
            }

            $theaters = [];
            foreach ($theaterIds as $theaterId) {
                $theaters[] = $this->getPlace($theaterId);
            }

            $schedGroups = [];
            $theaterGroups = [];

            foreach ($schedule as $item) {
                $dayCode = $this->getDayCode($item);
                $schedGroups[$dayCode]['block'][$item->PlaceObjectID][] = $item;
            }

            foreach ($theaters as $theater) {
                $theaterGroups[$theater->ObjectID] = $theater;
            }

            return [
                'schedule' => $schedGroups,
                'theaters' => $theaterGroups,
            ];
        });
    }

    /**
     * @param $item
     *
     * @return int
     */
    private function getDayCode($item)
    {
        $date = explode(' ', $item->DateTime);

        $dateValue = Carbon::parse($date[0]);

        $res = $dateValue->timestamp;

        return $res;
    }

    /**
     * @return array
     * @throws \Throwable
     */
    public function options(): array
    {
        return [];
    }
}
