<?php

namespace Beebmx\LaravelPay\Console;

use Beebmx\LaravelPay\Factory;
use Illuminate\Console\Command;

class Webhook extends Command
{
    protected $signature = 'pay:webhooks
                            {--driver= : Define a driver to use in a webhook endpoint}
                            {--url= : The URL endpoint to create a new webhook}
                            {--l|list : List all driver webhooks in a driver service}
                            {--destroy : Destroy all driver webhooks in a driver service}';

    protected $description = 'Create remote driver webhook endpoint to interact with.';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $service = $this->option('driver') ?? config('pay.default');
        $url = $this->option('url') ?? $this->getUrl();
        $list = $this->option('list');
        $destroy = $this->option('destroy');

        $driver = new (Factory::make($service));

        return match (true) {
            ! $destroy && ! $list => $this->createWebhook($driver, $url),
            $list => $this->listWebhooks($driver, $url),
            $destroy => $this->destroyWebhooks($driver, $url),
        };
    }

    protected function createWebhook($driver, $url): int
    {
        if ($this->confirm("Do you really want to create a webhook for {$driver->getName()} driver?", false)) {
            $driver->webhook($url);

            return Command::SUCCESS;
        }

        return Command::FAILURE;
    }

    protected function listWebhooks($driver, $url): int
    {
        $this->table(
            ['ID', 'Status', 'URL', 'Production'],
            $driver->webhookList($url)
        );

        return Command::SUCCESS;
    }

    protected function destroyWebhooks($driver, $url): int
    {
        if ($this->confirm("Do you really want to destroy all webhooks for {$driver->getName()} driver?", false)) {
            $driver->webhookDestroy($url);

            return Command::SUCCESS;
        }

        return Command::FAILURE;
    }

    protected function getUrl(): string
    {
        return implode('/', [
            config('app.url'),
            config('pay.path'),
            config('pay.webhooks'),
        ]);
    }
}
