<?php

test('application boots', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
});
