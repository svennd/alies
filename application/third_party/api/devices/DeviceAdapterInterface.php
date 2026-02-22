<?php

// common contract for all device adapters

interface DeviceAdapterInterface {
    public function parse( array $input );
}