<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class GenerateEspayKeys extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'espay:generate-keys';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate RSA private and public keys for Espay Payment Gateway';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Generating RSA keys for Espay...');

        // Create keys directory if it doesn't exist
        $keysPath = storage_path('app/keys/espay');
        
        if (!is_dir($keysPath)) {
            mkdir($keysPath, 0755, true);
            $this->info("Created directory: {$keysPath}");
        }

        // Generate RSA key pair
        $config = [
            "private_key_bits" => 2048,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        ];

        // Create the private and public key
        $res = openssl_pkey_new($config);

        if (!$res) {
            $this->error('Failed to generate RSA key pair!');
            $this->error('OpenSSL Error: ' . openssl_error_string());
            return 1;
        }

        // Extract the private key
        openssl_pkey_export($res, $privateKey);

        // Extract the public key
        $publicKeyDetails = openssl_pkey_get_details($res);
        $publicKey = $publicKeyDetails["key"];

        // Save private key
        $privateKeyPath = $keysPath . '/private_key.pem';
        file_put_contents($privateKeyPath, $privateKey);
        $this->info("Private key saved to: {$privateKeyPath}");

        // Save public key
        $publicKeyPath = $keysPath . '/public_key.pem';
        file_put_contents($publicKeyPath, $publicKey);
        $this->info("Public key saved to: {$publicKeyPath}");

        // Set permissions
        chmod($privateKeyPath, 0600);
        chmod($publicKeyPath, 0644);

        $this->newLine();
        $this->info('✅ RSA keys generated successfully!');
        $this->newLine();
        $this->warn('IMPORTANT: You need to share the PUBLIC KEY with Espay team!');
        $this->newLine();
        $this->info('Public Key:');
        $this->line('-----------------------------------------------------------');
        $this->line($publicKey);
        $this->line('-----------------------------------------------------------');
        $this->newLine();
        $this->info('Send this public key to Espay support team so they can configure it in their system.');
        $this->newLine();
        $this->warn('⚠️  NEVER share your private key with anyone!');
        $this->newLine();

        return 0;
    }
}