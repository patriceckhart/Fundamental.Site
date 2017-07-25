<?php
namespace Fundamental\Site\Controller;

/*
 * This file is part of the Fundamental.Site package.
 */

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\Controller\ActionController;
use Neos\Eel\FlowQuery\FlowQuery;
use Neos\Eel\FlowQuery\Operations;

class NewsController extends ActionController
{

    /**
     * @Flow\Inject
     * @var \Neos\ContentRepository\Domain\Service\ContextFactoryInterface
     */
    protected $contextFactory;


    /**
     * @return void
     */
    public function indexAction() {
        $newsarticles = $this->request->getInternalArgument('__newsarticles');
        $nodeident = $this->request->getInternalArgument('__nodeident');
        $pagebrowser = $this->request->getInternalArgument('__pagebrowser');

        $this->view->assign('nodeident', $nodeident);
        $this->view->assign('pageBrowser', $pagebrowser);

        $workspaceName = "live";

        if ($newsarticles=="") {
            $itemsPerPage = 4;
        } else {
            $itemsPerPage = $newsarticles;
        }

        $context = $this->contextFactory->create(array('workspaceName' => $workspaceName));

        /*if ($nodeident=="") {

            $node = $context->getNodeByIdentifier('b557193f-e818-4792-b2cc-051795d04d25');
        } else {
            $node = $context->getNodeByIdentifier($nodeident);
        }*/

        $node = $context->getNodeByIdentifier($nodeident);

        $articles = (new FlowQuery(array($node)))->children('[instanceof Fundamental.Site:NewsPage]')->context(array('workspaceName' => 'live'))->get();

        $pathsegment = $node->getProperty('uriPathSegment');
        $this->view->assign('pathsegment', $pathsegment);

        if ($this->request->hasArgument('page')) {
            $page = $this->request->getArgument('page');
        } else {
            $page = 1;
        }

        $resultsCount = count($articles);
        if ($resultsCount <= $itemsPerPage) {
            $articles2 = $articles;
        } else {
            $queryOffset = $itemsPerPage * ($page - 1);
            $queryItems = $itemsPerPage * $page;

            $this->view->assign('queryOffset', $queryOffset);

            $articles2 = (new FlowQuery(array($node)))->children('[instanceof Fundamental.Site:NewsPage]')->context(array('workspaceName' => 'live'))->sort('_index', 'DESC')->slice($queryOffset, $queryItems)->get();

            $pages = ceil($resultsCount / $itemsPerPage);
            if ($pages <= 10) {
                $minPagination = 1;
                $maxPagination = $pages;
            }
            if ($pages > 10) {
                $maxPagination = $page + 5;
                if ($maxPagination > $pages) {
                    $maxPagination = $pages;
                }
                $minPagination = $maxPagination - 11;
                if ($minPagination < 1) {
                    $minPagination = 1;
                    $maxPagination = 11;
                }
            }
            for ($i = $minPagination; $i <= $maxPagination; $i++) {
                $pagination['pages'][$i]['text'] = $i;
            }
            if ($page > 1) {
                $pagination['prev'] = $page - 1;
            }
            if ($page < $pages) {
                $pagination['next'] = $page + 1;
            }
            if ($minPagination > 1) {
                $pagination['first'] = 1;
            }
            if ($maxPagination < $pages) {
                $pagination['last'] = $pages;
            }
        }

        $pagination['current'] = $page;
        $this->view->assign('pagination', $pagination);

        $this->view->assign('items', $articles2);

    }

}
