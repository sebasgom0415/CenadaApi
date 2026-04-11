<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateApiToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'simm:api-token {email? : Email del usuario}';

    protected $description = 'Genera un nuevo API token para un usuario';

    public function handle()
    {
        $email = $this->argument('email') ?? 'admin@cenada.cr';
        $user  = \App\Models\User::where('email', $email)->first();

        if (!$user) {
            $this->error("Usuario $email no encontrado.");
            return 1;
        }

        $plainToken = \Illuminate\Support\Str::random(60);
        $user->update(['api_token' => hash('sha256', $plainToken)]);

        $this->info("Token generado para: {$user->email}");
        $this->newLine();
        $this->line("  <comment>Token (guárdalo, no se mostrará de nuevo):</comment>");
        $this->line("  <info>$plainToken</info>");
        $this->newLine();
        $this->line("  Uso con header:  Authorization: Bearer $plainToken");
        $this->line("  Uso con query:   ?api_token=$plainToken");

        return 0;
    }
}
