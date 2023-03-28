<?php
namespace MaximeRainville\GithubAudit\Admin;

use MaximeRainville\GithubAudit\Models\Organisation;
use MaximeRainville\GithubAudit\Models\Repository;
use MaximeRainville\GithubAudit\Models\RepoUser;
use MaximeRainville\GithubAudit\Models\User;
use SilverStripe\Admin\ModelAdmin;

class GitHub extends ModelAdmin
{

    private static $url_segment = 'github';

    private static $menu_title = 'GitHub';

    private static $managed_models = [
        'organisation' => [
            'title' => 'Organisations',
            'dataClass' => Organisation::class
        ],
        'repository' => [
            'title' => 'Repositories',
            'dataClass' => Repository::class
        ],
        'user' => [
            'title' => 'Users',
            'dataClass' => User::class
        ],
        'repo-user' => [
            'title' => 'Repo Users',
            'dataClass' => RepoUser::class
        ],
    ];

}

