<?php

namespace MaximeRainville\GithubAudit\Models\Packagist;

use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\ManyManyThroughList;

/**
 * @method ManyManyThroughList|User[] Users()
 * @property int $GithubId
 * @property string $Name
 * @property int $OrganisationID
 * @property Organisation $Organisation
 */
class Package extends DataObject
{
    private static $table_name = 'PackagistPackage';

    private static $db = [
        'Title' => 'Varchar(255)',
        'Repository' => 'Varchar(255)',
        'Notes' => 'Text',
        'Skip' => 'Boolean',
    ];

    private static $has_one = [
        'Organisation' => Organisation::class,
    ];

    private static $many_many = [
        'Maintainers' => [
            'through' => PackageMaintainer::class,
            'from' => 'Package',
            'to' => 'Maintainer',
        ]
    ];

    private static $defaults = [
        'Skip' => false,
    ];

    private static $summary_fields = [
        'Title' => 'Title',
        'Repository' => 'Repository',
        'Organisation.Title' => 'Organisation',
        'Maintainers.Count' => '# Maintainers',
    ];

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->fieldByName('Root.Main.Skip')->setDescription('Some repos (e.g. Private temporary forks attached to security notices) should not be included in the report.');

        return $fields;
    }

    public function getRemoteLink()
    {
        return 'https://packagist.org/packages/' . $this->Organisation->title . '/' . $this->Title;
    }

}
