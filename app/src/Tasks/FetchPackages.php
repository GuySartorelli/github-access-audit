<?php

namespace MaximeRainville\GithubAudit\Tasks;

use Exception;
use Generator;
use MaximeRainville\GithubAudit\Models\Packagist\Organisation;
use MaximeRainville\GithubAudit\Models\Packagist\Package;
use MaximeRainville\GithubAudit\Models\Packagist\Maintainer;
use SilverStripe\Dev\BuildTask;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class FetchPackages extends BuildTask
{

    private static $segment = 'fetch-packages';

    private array $failed_repos = [];

    private HttpClientInterface $client;

    public function __construct()
    {
        $this->client = HttpClient::create();
        parent::__construct();
    }

    public function run($request)
    {
        $this->loopOrgs();

        if ($this->failed_repos) {
            echo "\n\nCould not fetch data for the following repositories:\n";
            foreach ($this->failed_repos as $repo) {
                echo "- $repo\n";
            }
        }
    }

    private function loopOrgs(): void
    {
        $orgs = Organisation::get();

        foreach ($orgs as $org) {
            echo "\nFetching Repos for {$org->Title}\n";
            $this->loopPackages($org);
        }
    }

    private function loopPackages(Organisation $org): void
    {
        foreach ($this->packagesFetcher($org->Title) as $packageName) {
            echo "- processing {$packageName}\n";
            $package = $org->Packages()->filter('Title', $packageName)->first();
            if (!$package) {
                $package = Package::create();
            } elseif ($package->Skip) {
                continue;
            }

            $package->Title = $packageName;
            $package->OrganisationID = $org->ID;
            try {
                $this->addPackageData($org, $package);
            } catch (Exception $ex) {
                $package->Notes = $ex->getMessage() . "\n" . $ex->getTraceAsString();
                echo "REPO FETCHING Failed: {$ex->getMessage()}\n";
                $this->failed_repos[] = $org->Name . '/' . $packageName;
            } finally {
                $package->write();
            }
        }
    }

    private function packagesFetcher(string $org): Generator
    {
        $this->throttle();
        $response = $this->client->request('GET', 'https://packagist.org/packages/list.json?vendor=' . $org);
        $data = $response->toArray();
        foreach ($data['packageNames'] as $package) {
            yield str_replace($org . '/', '', $package);
        }
        return;
    }

    private function addPackageData(Organisation $org, Package $package): void
    {
        $package->Maintainers()->removeAll();
        $packageData = $this->fetchPackageData($org->Title, $package->Title);

        foreach ($packageData['maintainers'] as $maintainerData) {
            $maintainer = Maintainer::get()->filter('Title', $maintainerData['name'])->first();
            if (!$maintainer) {
                $maintainer = Maintainer::create();
            }

            $maintainer->Title = $maintainerData['name'];
            $maintainer->AvatarUrl = $maintainerData['avatar_url'];
            $maintainer->write();
            $package->Maintainers()->add($maintainer);
        }

        $package->Repository = $packageData['repository'];
    }

    private function fetchPackageData(string $org, string $package): array
    {
        $this->throttle();
        $response = $this->client->request('GET', "https://packagist.org/packages/{$org}/{$package}.json");
        return $response->toArray()['package'];
    }

    private function throttle(): void
    {
        usleep(100000);
    }
}
