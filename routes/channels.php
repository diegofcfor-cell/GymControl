<?php

use Illuminate\Support\Facades\Broadcast;

// El canal `aforo` es público (App\Events\AforoActualizado): no necesita autorización porque solo transmite
// cantidades. Los canales privados se autorizarían acá.
Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});
