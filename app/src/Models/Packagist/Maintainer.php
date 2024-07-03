<?php

namespace MaximeRainville\GithubAudit\Models\Packagist;

use SilverStripe\Forms\FormField;
use SilverStripe\Forms\LiteralField;
use SilverStripe\ORM\DataObject;
use SilverStripe\View\ArrayData;

class Maintainer extends DataObject
{
    private static $table_name = 'PackagistMaintainer';

    private static $db = [
        'Title' => 'Varchar(255)',
        'AccessReview' => 'Enum("TODO, GOOD, BAD", "TODO")',
        'AvatarUrl' => 'Varchar(255)',
        'Note' => 'Text',
    ];

    private static $belongs_many_many = [
        'Packages' => Package::class,
    ];

    private static $summary_fields = [
        'Avatar' => 'Avatar',
        'Title' => 'Login',
        'PackagesAccessSummary' => 'Packages',
        'AccessReview' => 'Access Review',
        'Note' => 'Note'
    ];

    private static $searchable_fields = [
        'Title',
        'AccessReview',
        'Note',
    ];

    public function PackagesCount(): int
    {
        return $this->Packages()->Count();
    }

    public function getProfileLink()
    {
        return $this->renderWith(self::class . '_ProfileLink');
    }

    public function getAvatar()
    {
        return $this->renderWith(self::class . '_Avatar');
    }

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $login = ArrayData::create(['Title' => 'Login', 'Name' => $this->Title])->renderWith(
            FormField::class . '_holder',
            ['Field' => $this->getProfileLink()]
        );

        $fields->replaceField(
            'Title',
            LiteralField::create('Packagist Link', $login)
        );

        $fields->removeByName('AvatarUrl');

        return $fields;
    }

    public function getPackagesAccessSummary()
    {
        $packages = $this->Packages()->map('ID', 'Title')->toArray();
        return implode(', ', $packages);
    }
}
