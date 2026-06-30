<?php
namespace SocialAuth;

use SocialAuth\Database\DbManager;

class Activator {

    public static function activate(): void {
        DbManager::install();
    }
}
