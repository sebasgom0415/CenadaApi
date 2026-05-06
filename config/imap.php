<?php

return [
    'host'       => env('IMAP_HOST', 'imap.gmail.com'),
    'port'       => env('IMAP_PORT', 993),
    'encryption' => env('IMAP_ENCRYPTION', 'ssl'),
    'user'       => env('IMAP_USER'),
    'pass'       => env('IMAP_PASS'),
    'folder'     => env('IMAP_FOLDER', 'INBOX'),
];
