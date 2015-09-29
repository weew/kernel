<?php

namespace Tests\Weew\Kernel\Mocks;

use Weew\Foundation\IDictionary;

class SharedFakeProvider {
    public $status;

    public function initialize(IDictionary $shared) {
        $this->status = $shared->get('status');
    }

    public function boot(IDictionary $shared) {
        $this->status = $shared->get('status');
    }

    public function shutdown(IDictionary $shared) {
        $this->status = $shared->get('status');
    }
}
