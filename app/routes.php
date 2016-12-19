<?php
// Routes

$app->get('/[{amount_region}]', App\Action\LandscapesOfBrazil::class);

$app->get('/{amount_region}/[{amount_local}]', App\Action\LandscapesOfBrazil::class);
