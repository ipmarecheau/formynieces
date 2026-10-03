<?php

it('reports a commit and environment for the parity check', function () {
    $this->getJson('/version')
        ->assertOk()
        ->assertJsonStructure(['commit', 'env']);
});
