<?php 
namespace App\Filament\App\Pages\Tenancy;

use App\Models\Team;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Tenancy\RegisterTenant;
use Illuminate\Support\Facades\Auth;
class RegisterTeam extends RegisterTenant
{
    public static function getLabel(): string
    {
        return 'Register team';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name'),
                TextInput::make('slug'),
                // ...
            ]);
    }

    protected function handleRegistration(array $data): Team
    {
       // Get the authenticated user FIRST
       $user = Auth::user();
        
       if (!$user) {
           abort(403, 'You must be logged in to create a team');
       }
$team = Team::create($data);
\Log::debug('Team created', ['team_id' => $team->id]);

$user = auth()->user();
\Log::debug('Attaching user', ['user' => $user->id]);
$team->members()->attach($user, ['role' => 'admin']);

\Log::debug('Current teams', ['teams' => $user->teams]);
        return $team;
    }
}