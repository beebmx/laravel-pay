<?php

namespace Beebmx\LaravelPay\Concerns;

use Beebmx\LaravelPay\Elements\Customer;
use Beebmx\LaravelPay\Exceptions\CustomerAlreadyExists;
use Beebmx\LaravelPay\Exceptions\InvalidCustomer;

trait ManageCustomer
{
    public function isCustomer(): bool
    {
        return !is_null($this->service_customer_id);
    }

    /**
     * @throws InvalidCustomer
     */
    public function asCustomer(): Customer
    {
        $this->hasValidCustomerDriver();

        return new Customer($this, $this->driver()->asCustomer($this->service_customer_id));
    }

    public function asAnonymousCustomer(array $options = []): Customer
    {
        $options = $this->getCustomerOptions($options);

        return new Customer($this, tap($this->replicate(), function($user) use ($options) {
            $user->id = null;
            $user->name = $options['name'];
            $user->email = $options['email'];
            $user->phone = $options['phone'] ?? null;
        }));
    }

    /**
     * @throws CustomerAlreadyExists
     */
    public function createCustomerWithDriver(array $options = []): Customer
    {
        if ($this->isCustomer()) {
            throw CustomerAlreadyExists::exists($this);
        }

        $options = $this->getCustomerOptions($options);

        $customer = $this->driver()->customerCreate($options);

        $this->service = $this->getDefaultDriverName();
        $this->service_customer_id = $customer->id;
        $this->save();

        return new Customer($this, $customer);
    }

    public function driverName()
    {
        return $this->name;
    }

    public function driverEmail()
    {
        return $this->email;
    }

    public function driverPhone()
    {
        return $this->phone;
    }

    protected function getCustomerOptions(array $options = []) :array
    {
        if (! array_key_exists('name', $options) && $name = $this->driverName()) {
            $options['name'] = $name;
        }

        if (! array_key_exists('email', $options) && $email = $this->driverEmail()) {
            $options['email'] = $email;
        }

        if (! array_key_exists('phone', $options) && $phone = $this->driverPhone()) {
            $options['phone'] = $phone;
        }

        return $options;
    }

    /**
     * @throws InvalidCustomer
     */
    protected function hasValidCustomerDriver(): void
    {
        if (!$this->isCustomer()) {
            throw InvalidCustomer::exists($this);
        }
    }
}