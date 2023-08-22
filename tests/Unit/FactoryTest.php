<?php

namespace Beebmx\LaravelPay\Tests\Unit;

use Beebmx\LaravelPay\Exceptions\DriverNonInstantiable;
use Beebmx\LaravelPay\Exceptions\InvalidDriver;
use Beebmx\LaravelPay\Factory;
use Beebmx\LaravelPay\Tests\Fixtures\User;
use Beebmx\LaravelPay\Tests\TestCase;

class FactoryTest extends TestCase
{
    /** @test */
    public function a_factory_class_exists()
    {
        $this->assertTrue(class_exists(Factory::class));
    }

    /**
     * @test
     *
     * @define-env useSandboxDriver
     */
    public function it_returns_the_default_driver()
    {
        $this->assertEquals(
            \Beebmx\LaravelPay\Drivers\SandboxDriver::class,
            Factory::getService()
        );
    }

    /**
     * @test
     *
     * @define-env useInvalidDriver
     */
    public function it_throws_an_exception_when_default_driver_is_invalid()
    {
        $this->expectException(InvalidDriver::class);

        Factory::getService();
    }

    /**
     * @test
     *
     * @define-env useDriverWithNoClass
     */
    public function it_throws_an_exception_when_driver_has_not_driver_class()
    {
        $this->expectException(DriverNonInstantiable::class);

        Factory::make();
    }

    /**
     * @test
     *
     ** @define-env useSandboxDriver
     */
    public function it_returns_an_instance_of_different_default_driver()
    {
        $this->assertEquals(
            \Beebmx\LaravelPay\Drivers\StripeDriver::class,
            Factory::make('stripe')
        );
    }

    /**
     * @test
     *
     * @define-env useDrivers
     */
    public function it_returns_all_the_driver_classes_available()
    {
        $this->assertCount(3, Factory::getDriversClasses());
        $this->assertEquals(
            \Beebmx\LaravelPay\Drivers\SandboxDriver::class,
            Factory::getDriversClasses()['sandbox']
        );
        $this->assertEquals(
            \Beebmx\LaravelPay\Drivers\StripeDriver::class,
            Factory::getDriversClasses()['stripe']
        );
        $this->assertEquals(
            \Beebmx\LaravelPay\Drivers\ConektaDriver::class,
            Factory::getDriversClasses()['conekta']
        );
    }

    /** @test */
    public function it_finds_the_right_driver_for_a_customer()
    {
        $user = new User;
        $user->service = 'sandbox';

        $this->assertEquals(
            \Beebmx\LaravelPay\Drivers\SandboxDriver::class,
            $user->getCustomerDriver()
        );
    }

    /** @test */
    public function it_throws_an_exception_if_request_a_driver_without_be_setting_before()
    {
        $user = new User;

        $this->expectException(InvalidDriver::class);
        $user->getCustomerDriver();
    }

    protected function useSandboxDriver($app)
    {
        $app['config']->set('pay.default', 'sandbox');
    }

    protected function useInvalidDriver($app)
    {
        $app['config']->set('pay.default', 'invalid');
    }

    protected function useDriverWithNoClass($app)
    {
        $app['config']->set('pay.default', 'error');
        $app['config']->set('pay.drivers', ['error' => ['driver']]);
    }

    protected function useDrivers($app)
    {
        $app['config']->set('pay.drivers',
            [
                'sandbox' => [
                    'driver' => \Beebmx\LaravelPay\Drivers\SandboxDriver::class,
                ],

                'stripe' => [
                    'driver' => \Beebmx\LaravelPay\Drivers\StripeDriver::class,
                ],

                'conekta' => [
                    'driver' => \Beebmx\LaravelPay\Drivers\ConektaDriver::class,
                ],
            ],
        );
    }
}
