<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Music;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class WaveSurferPage extends Page
{
    public Music $music;
    protected static bool $shouldRegisterNavigation = false;
    protected static ?string $slug = 'wave-surfer/{music}';

    protected string $view = 'filament.pages.wave-surfer';



    public ?string $waveform = null;

    public function mount(Music $music): void
    {
        $this->music = $music->load('release');
    }
    public function getTitle(): string
    {
        return $this->music->title . ' - ' . $this->music->release->title;
    }
}
