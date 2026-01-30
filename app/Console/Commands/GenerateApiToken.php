<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class GenerateApiToken extends Command
{
    protected $signature = 'api:token {email} {--name=api-token}';
    protected $description = 'Generate a perpetual API token for Api Access';
    public function handle()
    {
        $user = User::where('email', $this->argument('email'))->first();
        if (!$user) {
            $this->error('User not found!');
            return 1;
        }
        $user->tokens()->delete();
        $user->tokens()->where('name', $this->option('name'))->delete();
        $token = $user->createToken(
            $this->option('name'),
            ['*'],
            null
        );
        $this->info('Token generated successfully:');
        $this->line($token->plainTextToken);
        $this->addTokenToEnv($token->plainTextToken);
        return 0;
    }
    private function addTokenToEnv($token)
    {
        if ($this->confirm('Do you want to save this token in .env file?')) {
            $envFile = base_path('.env');
            $content = file_get_contents($envFile);
            if (strpos($content, 'API_TOKEN=') !== false) {
                $content = preg_replace('/API_TOKEN=.*/', 'API_TOKEN=' . $token, $content);
            } else {
                $content .= "\nAPI_TOKEN=" . $token;
            }
            
            file_put_contents($envFile, $content);
            $this->info('Token saved to .env file');
        }
    }
}