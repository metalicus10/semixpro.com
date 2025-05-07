<?php

namespace App\Orchid\Screens;

use App\Models\BulkLog;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class BulkLogsScreen extends Screen
{
    public $name = 'Журнал массовых изменений';

    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [
            'logs' => BulkLog::with('user')->latest()->paginate(20),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'BulkLogsScreen';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [];
    }

    public function authorize($ability, $arguments = []): bool
    {
        return auth()->user()?->hasAccess('platform.admin');
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): iterable
    {
        return [
            Layout::table('logs', [
                TD::make('id', 'ID')->sort(),
                TD::make('user_id', 'Пользователь')->render(fn($log) => $log->user->name ?? '-'),
                TD::make('summary', 'Описание')->width('50%'),
                TD::make('created_at', 'Дата')->render(fn($log) => $log->created_at->format('d.m.Y H:i')),
                TD::make('Подробнее')->render(function ($log) {
                    return Link::make('Открыть')
                        ->modal('DetailsModal')
                        ->modalTitle('Детали изменений')
                        ->method('showDetails')
                        ->asyncParameters([
                            'log_id' => $log->id
                        ]);
                }),
            ]),

            Layout::modal('DetailsModal', [
                Layout::view('admin.bulk-log-details'),
            ])->async('asyncDetails'),
        ];
    }

    public function asyncDetails(int $log_id): array
    {
        $log = BulkLog::findOrFail($log_id);
        return ['log' => $log];
    }
}
