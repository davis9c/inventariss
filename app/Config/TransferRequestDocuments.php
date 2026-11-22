<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class TransferRequestDocuments extends BaseConfig
{
    public array $allowedMimes = [
        'image/jpeg',
        'image/png',
        'application/pdf',
    ];

    public array $allowedExtensions = [
        'jpg',
        'jpeg',
        'png',
        'pdf',
    ];

    public int $maxSizeBytes = 5_242_880;

    public int $maxFiles = 1;

    public string $directory = 'transfer-request-evidence';

    public array $inlineMimes = [
        'image/jpeg',
        'image/png',
        'application/pdf',
    ];
}