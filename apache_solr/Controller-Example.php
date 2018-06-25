<?php

namespace App\Controllers;

use App\Services\SolrService;

class SearchController
{
    
    private $solr;
    
    public function __construct(SolrService $solr)
    {
        $this->solr = $solr;
    }

    
    public function __invoke($params)
    {   

        if(isset($_GET['q']) && strlen(trim(strip_tags($_GET['q']))) > 0 )
        {
            $query = trim(strip_tags($_GET['q']));
            $this->solr->setQuery($query);
            $this->solr->setLimit(10);

            if(isset($_GET['limit']) && ( is_numeric($_GET['limit']) || $_GET['limit'] === 'all') )
            {
                $this->solr->setLimit($_GET['limit']);
            }

            $this->solr->setResults();

        }

        header("Access-Control-Allow-Origin: *");
        header("Content-type: application/json; charset=utf-8");

        echo $this->solr->getResults();

    }
   
}
