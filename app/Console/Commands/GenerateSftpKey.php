<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use phpseclib3\Crypt\RSA;
use Illuminate\Support\Facades\Log;

class GenerateSftpKey extends Command
{
    protected $signature = 'sftp:generate-key';
    protected $description = 'Generate SFTP private key in the correct format';

    public function handle()
    {
        $this->info('Generating new SFTP key pair...');
        $keyPath = env('GENERATE_SFTP_PRIVATE_PATH', 'ssh');
        $privateKeyPath = env('GENERATE_SFTP_PRIVATE_KEY');
        $publicKeyPath = env('GENERATE_SFTP_PUBLIC_KEY');
        Storage::disk('ssh')->makeDirectory($keyPath);
        $private = RSA::createKey(2048);
        Storage::disk('ssh')->put($privateKeyPath, $private->toString('PKCS1'));
        Storage::disk('ssh')->put($publicKeyPath, $private->getPublicKey()->toString('OpenSSH'));
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $privateKeyFullPath = Storage::disk('ssh')->path($privateKeyPath);
            $publicKeyFullPath = Storage::disk('ssh')->path($publicKeyPath);
            exec('icacls "' . $privateKeyFullPath . '" /inheritance:r /grant:r "%USERNAME%":F');
            exec('icacls "' . $publicKeyFullPath . '" /inheritance:r /grant:r "%USERNAME%":F');
        }
        $this->info('Keys generated successfully!');
        $this->info('Private key: ' . Storage::disk('ssh')->path($privateKeyPath));
        $this->info('Public key: ' . Storage::disk('ssh')->path($publicKeyPath));
        $this->info('Please provide the public key content to your SFTP server administrator:');
        $this->line(Storage::disk('ssh')->get($publicKeyPath));
        $this->info('Key Details:');
        $this->line('Private Key Format: ' . $private->toString('PKCS1'));
        $this->line('Public Key Format: ' . $private->getPublicKey()->toString('OpenSSH'));
    }
}
