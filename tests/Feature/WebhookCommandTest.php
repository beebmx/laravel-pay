<?php

namespace Beebmx\LaravelPay\Tests\Feature;

use Beebmx\LaravelPay\Exceptions\WebhookUnavailable;
use Beebmx\LaravelPay\Tests\TestCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class WebhookCommandTest extends TestCase
{
    /**
     * @test
     *
     * @define-env useSandboxDriver
     */
    public function a_webhook_command_takes_the_default_driver()
    {
        $this->expectException(WebhookUnavailable::class);

        $this->artisan('pay:webhooks')
            ->expectsConfirmation('Do you really want to create a webhook for sandbox driver?', 'yes');
    }

    /**
     * @test
     *
     * @define-env useSandboxDriver
     */
    public function a_webhook_command_can_not_list_in_sandbox()
    {
        $this->expectException(WebhookUnavailable::class);

        $this->artisan('pay:webhooks --list');
    }

    /**
     * @test
     *
     * @define-env useSandboxDriver
     */
    public function a_webhook_command_can_not_destroy_in_sandbox()
    {
        $this->expectException(WebhookUnavailable::class);

        $this->artisan('pay:webhooks --destroy')
            ->expectsConfirmation('Do you really want to destroy all webhooks for sandbox driver?', 'yes');
    }

    /**
     * @test
     *
     * @define-env useConektaDriver
     */
    public function a_webhook_command_its_not_available_for_sandbox()
    {
        $this->expectException(WebhookUnavailable::class);

        $this->artisan('pay:webhooks --driver=sandbox')
            ->expectsConfirmation('Do you really want to create a webhook for sandbox driver?', 'yes');
    }

    /**
     * @test
     *
     * @define-env useConektaDriver
     */
    public function a_webhook_command_for_conekta_creates_an_endpoint()
    {
        $this->artisan('pay:webhooks')
            ->expectsConfirmation('Do you really want to create a webhook for conekta driver?', 'no')
            ->assertExitCode(Command::FAILURE);
    }

    /**
     * @test
     *
     * @define-env useConektaDriver
     */
    public function a_webhook_command_list_all_the_webhooks_for_conekta_driver()
    {
        Http::fake([
            'api.conekta.io/*' => Http::response(['data' => $this->conektaListStubs()], 200),
        ]);

        $this->artisan('pay:webhooks --list')
            ->expectsTable(['ID', 'Status', 'URL', 'Production'], [[
                'id' => 'webhook_0123456789',
                'status' => 'connected',
                'url' => 'http://laravel-pay.test/pay/webhooks',
                'production' => '❎',
            ]])->assertExitCode(Command::SUCCESS);
    }

    /**
     * @test
     *
     * @define-env useConektaDriver
     */
    public function a_webhook_command_for_conekta_destroy_all_endpoints()
    {
        Http::fake([
            'api.conekta.io/*' => Http::response(['data' => $this->conektaListStubs()], 200),
        ]);

        $this->artisan('pay:webhooks --destroy')
            ->expectsConfirmation('Do you really want to destroy all webhooks for conekta driver?', 'yes')
            ->assertExitCode(Command::SUCCESS);
    }

    /**
     * @test
     *
     * @define-env useStripeDriver
     */
    public function a_webhook_command_for_stripe_creates_an_endpoint()
    {
        $this->artisan('pay:webhooks')
            ->expectsConfirmation('Do you really want to create a webhook for stripe driver?', 'no')
            ->assertExitCode(Command::FAILURE);
    }

    /**
     * @test
     *
     * @define-env useStripeDriver
     */
    public function a_webhook_command_list_all_the_webhooks_for_stripe_driver()
    {
        $this->artisan('pay:webhooks --list')
            ->expectsTable(['ID', 'Status', 'URL', 'Production'], [])
            ->assertExitCode(Command::SUCCESS);
    }

    /**
     * @test
     *
     * @define-env useStripeDriver
     */
    public function a_webhook_command_for_stripe_destroy_all_endpoints()
    {
        $this->artisan('pay:webhooks --destroy')
            ->expectsConfirmation('Do you really want to destroy all webhooks for stripe driver?', 'yes')
            ->assertExitCode(Command::SUCCESS);
    }

    protected function useSandboxDriver($app)
    {
        $app['config']->set('pay.default', 'sandbox');
    }

    protected function useConektaDriver($app)
    {
        $app['config']->set('pay.default', 'conekta');
    }

    protected function useStripeDriver($app)
    {
        $app['config']->set('pay.default', 'stripe');
    }

    protected function defineEnvironment($app)
    {
        $app['config']->set('app.url', 'http://laravel-pay.test');
    }

    protected function conektaListStubs(): array
    {
        return [[
            'id' => 'webhook_0123456789',
            'status' => 'connected',
            'url' => 'http://laravel-pay.test/pay/webhooks',
            'production_enabled' => false,
        ]];
    }

    protected function stripeListStubs(): array
    {
        return [[
            'id' => 'webhook_0123456789',
            'status' => 'connected',
            'url' => 'http://laravel-pay.test/pay/webhooks',
            'production_enabled' => false,
        ]];
    }
}
