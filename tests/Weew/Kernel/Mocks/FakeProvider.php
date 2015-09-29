<?php

namespace Tests\Weew\Kernel\Mocks;

class FakeProvider {
    public $status;

    public function initialize() {
        $this->status = 'initialized';
    }

    public function boot() {
        $this->status = 'booted';
    }

    public function shutdown() {
        $this->status = 'shutdown';
    }
}
