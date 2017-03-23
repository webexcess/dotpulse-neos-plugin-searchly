<?php
namespace Dotpulse\Searchly\Controller;

/*
 * This file is part of the Dotpulse.Searchly package.
 */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Mvc\Controller\ActionController;

/**
 * @Flow\Scope("singleton")
 */
class SearchlyController extends ActionController {

	/**
	 * @Flow\InjectConfiguration()
	 * @var array
	 */
	protected $settings;

    /**
     * @return void
     */
    public function indexAction() {
        $searchly__query = isset($_GET['searchly__query']) && $_GET['searchly__query'] != '' ? $_GET['searchly__query'] : null;
        $this->view->assign('searchValue', $searchly__query);

        if ( $searchly__query !== null ) {
            $params = $this->settings['params'];
            $client = new \Elasticsearch\Client($params);

            if ( $this->settings['fuzzy'] === true ) {
                // Fuzzy Search
                $searchParams['body']['query']['fuzzy_like_this']['fields'] = ['title', 'text'];
                $searchParams['body']['query']['fuzzy_like_this']['like_text'] = $searchly__query;
            } else {
                // $searchParams['body']['query']['match']['title'] = $searchly__query;
                $searchParams['body']['query']['multi_match']['query'] = $searchly__query;
                $searchParams['body']['query']['multi_match']['fields'] = ['title', 'text'];
            }
            
            $searchParams['body']['size'] = $this->settings['size'];
            $results = $client->search($searchParams);

            $this->view->assignMultiple(array(
                'searchTotal' => $results['hits']['total'],
                'searchResults' => $results['hits']['hits'],
                'crop' => $this->settings['crop']
            ));
        }

        $this->view->assign('attributes', $this->request->getInternalArgument('__attributes'));
    }

}
