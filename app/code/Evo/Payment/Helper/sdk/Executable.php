<?php

namespace Evo\sdk;

interface Executable {

    public function validate();

    public function execute($callback = NULL, $result_from_prev = array());
}
