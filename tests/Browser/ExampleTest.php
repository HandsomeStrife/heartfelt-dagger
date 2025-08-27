<?php

test('basic example', function () {
    $page = visit('/');
    
    $page
            ->assertSee('Laravel');
});