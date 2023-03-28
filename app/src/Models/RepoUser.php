<?php

namespace MaximeRainville\GithubAudit\Models;

use SilverStripe\ORM\DataObject;

class RepoUser extends DataObject
{
    private static $table_name = 'RepoUser';

    private static $db = [
        'Roles' => 'Varchar(255)'
    ];

    private static $has_one = [
        'Repository' => Repository::class,
        'User' => User::class,
    ];
}
