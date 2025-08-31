<?php

declare(strict_types=1);

test('homepage loads correctly', function () {
    visit('/')
        ->assertTitle('HeartfeltDagger')
        ->assertNoSmoke();
});