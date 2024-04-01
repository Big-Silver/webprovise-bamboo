<?php

class Travel
{
    public static function fetchData($url)
    {
        $json = file_get_contents($url);
        return json_decode($json, true);
    }
}

class Company
{
    private $companies;
    private $travels;

    public function __construct($companies, $travels)
    {
        $this->companies = $companies;
        $this->travels = $travels;
    }

    public function buildCompanyTree($parentId = "0")
    {
        $tree = [];
        foreach ($this->companies as &$company) {
            if ($company['parentId'] == $parentId) {
                $company['children'] = $this->buildCompanyTree($company['id']);
                $tree[] = $company;
            }
        }
        return $tree;
    }

    public function calculateTravelCost(&$companies)
    {
        $totalCost = 0;
        foreach ($companies as &$company) {
            $companyCost = 0;
            foreach ($this->travels as $travel) {
                if ($travel['companyId'] == $company['id']) {
                    $companyCost += $travel['price'];
                }
            }
            $company['cost'] = $companyCost + $this->calculateTravelCost($company['children']);
            $totalCost += $company['cost'];
        }
        return $totalCost;
    }
}

class TestScript
{
    public function execute()
    {
        $start = microtime(true);

        $companiesUrl = 'https://5f27781bf5d27e001612e057.mockapi.io/webprovise/companies';
        $travelsUrl = 'https://5f27781bf5d27e001612e057.mockapi.io/webprovise/travels';

        $companiesData = Travel::fetchData($companiesUrl);
        $travelsData = Travel::fetchData($travelsUrl);

        $companyObj = new Company($companiesData, $travelsData);
        $companyTree = $companyObj->buildCompanyTree();
        $companyObj->calculateTravelCost($companyTree);

        echo json_encode($companyTree, JSON_PRETTY_PRINT);

        echo 'Total time: '.  (microtime(true) - $start);
    }
}

(new TestScript())->execute();
