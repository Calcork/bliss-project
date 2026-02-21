<?php

return [

    'first_name' => new \App\Lib\Input\StringInputStructure(
        min_length: 1,
        max_length: 100,
    ),

    'last_name' => new \App\Lib\Input\StringInputStructure(
        min_length: 1,
        max_length: 100,
    ),

    'password' => new \App\Lib\Input\StringInputStructure(
        min_length: 8,
        max_length: 128,
    ),

    'email' => new \App\Lib\Input\StringInputStructure(
        regex: '[^@\s]+@[^@\s]+\.[^@\s]+$',
        max_length: 254,
    ),

];