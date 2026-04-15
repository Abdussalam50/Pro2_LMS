<?php

return [
    'project_id'         => env('FIREBASE_PROJECT_ID', 'eng-hash-458603-q4'),
    'api_key'            => env('FIREBASE_API_KEY'),
    'auth_domain'        => env('FIREBASE_AUTH_DOMAIN', 'eng-hash-458603-q4.firebaseapp.com'),
    'storage_bucket'     => env('FIREBASE_STORAGE_BUCKET', 'eng-hash-458603-q4.appspot.com'),
    'messaging_sender_id'=> env('FIREBASE_MESSAGING_SENDER_ID'),
    'app_id'             => env('FIREBASE_APP_ID'),
    'vapid_key'          => env('FIREBASE_VAPID_KEY'),
    'credentials'        => env('FIREBASE_CREDENTIALS', 'storage/app/firebase-credentials.json'),
];
