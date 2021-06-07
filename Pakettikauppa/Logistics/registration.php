<?php

\Magento\Framework\Component\ComponentRegistrar::register(
    \Magento\Framework\Component\ComponentRegistrar::MODULE,
    'Pakettikauppa_Logistics',
    isset($file) ? dirname($file) : __DIR__
);
