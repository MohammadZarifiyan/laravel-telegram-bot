<?php

namespace MohammadZarifiyan\Telegram\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;
use MohammadZarifiyan\Telegram\Exceptions\TelegramException;
use MohammadZarifiyan\Telegram\Facades\Telegram;
use MohammadZarifiyan\Telegram\Payloads\SetWebhookPayload;

class SetWebhook extends Command
{
    protected $signature = 'bot:set-webhook {--drop-pending-updates} {--api-key=} {--url=} {--max-connections=40}';

    protected $description = 'Sets Telegram webhook.';

    public function handle()
    {
        try {
			$payload = new SetWebhookPayload(
				$this->getUrl(),
				$this->hasOption('drop-pending-updates'),
				config('telegram.secure-token'),
				(int) $this->option('max-connections')
			);
			
            $response = Telegram::fresh($this->getApiKey())->execute($payload);

			$result = $response->object();

            if ($response->ok() && $result->ok) {
				$this->info('Webhook set successfully.');
				
				return 0;
            }

			$this->error('Failed to set webhook.');
			
			if (property_exists($result, 'description')) {
				$this->error($result->description);
			}

			return 1;
        }
        catch (TelegramException $exception) {
            $this->error(
				$exception->getMessage()
			);
	
			return 1;
        }
    }
	
	public function getUrl(): string
	{
		if ($url = $this->option('url')) {
			return $url;
		}
		
		$route_name = Config::get('telegram.update-route');
		
		return Url::route($route_name);
	}
	
	public function getApiKey(): string
	{
		return $this->option('api-key') ?: config('telegram.api-key');
	}
}
